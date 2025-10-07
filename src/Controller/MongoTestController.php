<?php

namespace App\Controller;

use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/test', name: 'mongo_test_')]
class MongoTestController extends AbstractController
{
    private DocumentManager $dm;

    public function __construct(DocumentManager $dm)
    {
        $this->dm = $dm;
    }

    #[Route('/mongo', name: 'connection', methods: ['GET'])]
    public function testMongoConnection(): JsonResponse
    {
        try {
            // Test de connexion simple
            $this->dm->getConnection()->selectDatabase('ecoride_mongo');
            
            return $this->json([
                'success' => true,
                'message' => 'Connexion MongoDB réussie',
                'database' => 'ecoride_mongo'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Erreur de connexion MongoDB: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ], 500);
        }
    }
}
