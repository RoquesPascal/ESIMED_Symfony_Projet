<?php

namespace App\Controller;

use App\Entity\Picture;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class PictureController extends AbstractController
{
    public function __invoke(Request $request): Picture
    {
        $uploadFile = $request->files->get('file');
        if(!$uploadFile)
        {
            throw new BadRequestException('"file" is required');
        }

        $picture = new Picture();
        $picture->setFile($uploadFile);
        $picture->setCreatedAt(new \DateTime());

        return $picture;
    }
}
