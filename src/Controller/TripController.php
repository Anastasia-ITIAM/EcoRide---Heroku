<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\Car;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/trip', name: 'trip_')]
class TripController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    // LISTER LES TRAJETS
    #[Route('/all', name: 'all', methods: ['GET'])]
    public function all(): JsonResponse
    {
        $trips = $this->em->getRepository(Trip::class)
            ->findBy(['status' => 'open'], ['departure_date' => 'ASC', 'departure_time' => 'ASC']);

        return new JsonResponse([
            'success' => true,
            'trips' => array_map([$this, 'formatTrip'], $trips)
        ]);
    }

    // RECHERCHE TRAJET
    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $depart = $request->query->get('depart');
        $arrivee = $request->query->get('arrivee');
        $datetime = $request->query->get('datetime');

        $qb = $this->em->getRepository(Trip::class)->createQueryBuilder('t');
        $qb->andWhere('t.status = :status')->setParameter('status', 'open');

        if ($depart || $arrivee || $datetime) {
            $orX = $qb->expr()->orX();

            if ($depart) {
                $orX->add($qb->expr()->like('LOWER(t.departure_address)', ':depart'));
                $orX->add($qb->expr()->like('LOWER(t.arrival_address)', ':depart'));
                $qb->setParameter('depart', '%' . strtolower($depart) . '%');
            }

            if ($arrivee) {
                $orX->add($qb->expr()->like('LOWER(t.departure_address)', ':arrivee'));
                $orX->add($qb->expr()->like('LOWER(t.arrival_address)', ':arrivee'));
                $qb->setParameter('arrivee', '%' . strtolower($arrivee) . '%');
            }

            if ($datetime) {
                $orX->add($qb->expr()->eq('t.departure_date', ':datetime'));
                $qb->setParameter('datetime', new \DateTime($datetime));
            }

            $qb->andWhere($orX);
        }

        $trips = $qb->orderBy('t.departure_date', 'ASC')
                    ->addOrderBy('t.departure_time', 'ASC')
                    ->getQuery()
                    ->getResult();

        return new JsonResponse([
            'success' => true,
            'trips' => array_map([$this, 'formatTrip'], $trips),
        ]);
    }

    // Détails d’un trajet par ID
    #[Route('/{id}', name: 'details', methods: ['GET'])]
    public function details(int $id): JsonResponse
    {
        $trip = $this->em->getRepository(Trip::class)->find($id);

        if (!$trip) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Trajet introuvable.'
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'trip' => $this->formatTrip($trip, true)
        ]);
    }

    // Lister les trajets réservés par l’utilisateur (chauffeur ou passager)
    #[Route('/reservation/list', name: 'reservation_list', methods: ['GET'])]
    public function reservationList(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return new JsonResponse(['success' => false, 'message' => 'Utilisateur non connecté.'], 401);
        }

        try {
            $qb = $this->em->getRepository(Trip::class)->createQueryBuilder('t')
                ->leftJoin('t.passengers', 'p')
                ->where('t.driver = :user OR p.id = :user')
                ->setParameter('user', $user->getId())
                ->orderBy('t.departure_date', 'ASC')
                ->addOrderBy('t.departure_time', 'ASC');

            $trips = $qb->getQuery()->getResult();

            return new JsonResponse([
                'success' => true,
                'trips' => array_map([$this, 'formatTrip'], $trips)
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Erreur récupération trajets : ' . $e->getMessage()
            ], 500);
        }
    }


    // Format d’un trajet
    private function formatTrip(Trip $trip, bool $withDetails = false): array
    {
        $driver = $trip->getDriver();
        $driverName = $driver ? $driver->getPseudo() : 'Inconnu';
        $driverPhoto = '/uploads/profiles/profile_default.svg';
        if ($driver && $driver->getProfilePhotoUrl()) {
            $filename = basename($driver->getProfilePhotoUrl());
            $driverPhoto = '/uploads/profiles/' . $filename;
        }

        $car = $trip->getCar();
        $vehicle = $car ? sprintf('%s %s (%s)', $car->getBrand(), $car->getModel(), $car->getColor()) : null;

        $data = [
            'id' => $trip->getId(),
            'car_id' => $car?->getId(),
            'user_id' => $driver?->getId(),
            'driver_name' => $driverName,
            'driver_photo_url' => $driverPhoto,
            'departure_address' => $trip->getDepartureAddress(),
            'arrival_address' => $trip->getArrivalAddress(),
            'departure_date' => $trip->getDepartureDate()?->format('Y-m-d'),
            'departure_time' => $trip->getDepartureTime()?->format('H:i'),
            'arrival_time' => $trip->getArrivalTime()?->format('H:i'),
            'available_seats' => $trip->getAvailableSeats(),
            'price' => $trip->getPrice(),
            'eco_friendly' => $trip->isEcoFriendly(),
            'status' => $trip->getStatus(),
            'finished' => $trip->isFinished(),
            'participant_validation' => $trip->isParticipantValidation(),
        ];

        if ($withDetails) {
            $data['vehicle'] = $vehicle;

            $passengers = [];
            foreach ($trip->getPassengers() as $passenger) {
                $passengers[] = [
                    'id' => $passenger->getId(),
                    'name' => $passenger->getPseudo(),
                    'email' => $passenger->getEmail(),
                ];
            }
            $data['passengers'] = $passengers;
        }

        return $data;
    }
}
