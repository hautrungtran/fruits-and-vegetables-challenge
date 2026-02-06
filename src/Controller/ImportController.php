<?php

namespace App\Controller;

use App\Form\ProduceImportType;
use App\Service\ProduceImporter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ImportController extends AbstractController
{
    #[Route('/import', name: 'app_import_produce', methods: ['POST'])]
    public function __invoke(
        Request $request,
        ProduceImporter $importer,
        NormalizerInterface $serializer,
    ): JsonResponse {
        $form = $this->createForm(ProduceImportType::class);
        $form->submit(array_merge($request->request->all(), $request->files->all()));
        if (!$form->isValid()) {
            return $this->json(
                ['errors' => $serializer->normalize($form->getErrors(true, true), 'json')],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $form->get('file')->getData();
        $file = $uploadedFile->getPathname();

        try {
            $result = $importer->importFromFile($file);
        } catch (\RuntimeException $exception) {
            return $this->json(['error' => $exception->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($result);
    }
}
