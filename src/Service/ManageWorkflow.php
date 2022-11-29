<?php

namespace App\Service;

use App\Repository\AdvertRepository;
use Symfony\Component\Workflow\Registry;

class ManageWorkflow
{
    public static function Publish($id, AdvertRepository $advertRepository, Registry $registry) : void
    {
        $advert = $advertRepository->find($id);
        $workflow = $registry->get($advert);
        if($workflow->can($advert, 'publish'))
        {
            $workflow->apply($advert, 'publish');
            $workflow->getEnabledTransitions($advert);
            $advert->setPublishedAt(new \DateTime());
            $advertRepository->save($advert, true);
        }
    }

    public static function Reject($id, AdvertRepository $advertRepository, Registry $registry) : void
    {
        $advert = $advertRepository->find($id);
        $workflow = $registry->get($advert);
        if($workflow->can($advert, 'reject'))
        {
            $workflow->apply($advert, 'reject');
            $workflow->getEnabledTransitions($advert);
            $advertRepository->save($advert, true);
        }
    }
}