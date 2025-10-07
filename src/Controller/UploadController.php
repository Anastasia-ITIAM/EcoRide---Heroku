<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/uploads', name: 'uploads_')]
class UploadController extends AbstractController
{
    #[Route('/profiles/{filename}', name: 'profile_image', methods: ['GET'])]
    public function serveProfileImage(string $filename): Response
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/' . $filename;
        
        if (!file_exists($filePath)) {
            // Retourner une image par défaut si le fichier n'existe pas
            $defaultPath = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles/profile_default.svg';
            if (file_exists($defaultPath)) {
                return new BinaryFileResponse($defaultPath);
            }
            throw $this->createNotFoundException('Image not found');
        }
        
        return new BinaryFileResponse($filePath);
    }
}
