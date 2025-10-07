<?php

namespace App\Controller;

use App\Document\TripReview;
use App\Entity\Trip;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/trip', name: 'trip_review_')]
class TripReviewController extends AbstractController
{
    private ?DocumentManager $dm;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(?DocumentManager $dm, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->dm = $dm;
        $this->em = $em;
        $this->logger = $logger;
    }

    // --- Ajouter un review ---
    #[Route('/{tripId}/reviews', name: 'add', methods: ['POST'])]
    public function add(int $tripId, Request $request): JsonResponse
    {
        try {
            if (!$this->dm) {
                return $this->json([
                    'success' => false, 
                    'message' => 'Service MongoDB non disponible'
                ], 503);
            }

            $data = json_decode($request->getContent(), true);

            if (!$data || empty($data['userId']) || empty($data['comment']) || !isset($data['rating'])) {
                return $this->json(['success' => false, 'message' => 'Données invalides.'], 400);
            }

            $trip = $this->em->getRepository(Trip::class)->find($tripId);
            if (!$trip) return $this->json(['success' => false, 'message' => 'Trajet introuvable.'], 404);

            $user = $this->em->getRepository(User::class)->find($data['userId']);
            if (!$user) return $this->json(['success' => false, 'message' => 'Utilisateur introuvable.'], 404);

            $review = new TripReview();
            $review->setTripId((string) $trip->getId())
                   ->setUserId((string) $user->getId())
                   ->setUserPseudo($user->getPseudo() ?? '')
                   ->setComment($data['comment'])
                   ->setRating((int) $data['rating'])
                   ->setCreatedAt(new \DateTime());

            $this->dm->persist($review);
            $this->dm->flush();

            return $this->json(['success' => true, 'review_id' => $review->getId()], 201);

        } catch (\Exception $e) {
            $this->logger->error('Erreur add review: ' . $e->getMessage(), ['exception' => $e]);
            return $this->json(['success' => false, 'message' => 'Erreur lors de l'ajout de l'avis.'], 500);
        }
    }

    // --- Lister les reviews ---
    #[Route('/{tripId}/reviews', name: 'list', methods: ['GET'])]
    public function getReviews(int $tripId): JsonResponse
    {
        try {
            if (!$this->dm) {
                return $this->json([
                    'success' => true, 
                    'reviews' => [],
                    'message' => 'Service MongoDB non disponible'
                ]);
            }

            $reviews = $this->dm->getRepository(TripReview::class)
                                ->findBy(['tripId' => (string) $tripId]) ?? [];

            $data = [];
            foreach ($reviews as $r) {
                $data[] = [
                    'id' => $r->getId(),
                    'tripId' => $r->getTripId(),
                    'userId' => $r->getUserId(),
                    'userPseudo' => $r->getUserPseudo(),
                    'comment' => $r->getComment(),
                    'rating' => $r->getRating(),
                    'createdAt' => $r->getCreatedAt()?->format('Y-m-d H:i')
                ];
            }

            return $this->json(['success' => true, 'reviews' => $data]);

        } catch (\Exception $e) {
            $this->logger->error('Erreur getReviews: ' . $e->getMessage(), ['exception' => $e]);
            
            return $this->json([
                'success' => true, 
                'reviews' => [],
                'message' => 'Aucun avis disponible pour le moment'
            ]);
        }
    }
}
