<?php

/**
 * ProTalk
 *
 * Copyright (c) 2012-2013, ProTalk
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Protalk\MediaBundle\Controller;

use Protalk\MediaBundle\Form\Media\ContributeMedia;
use Protalk\MediaBundle\Entity\Contribution;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Swift_Message;

class ContributionController extends Controller
{

    public function newAction(Request $request)
    {
        $contribution = new Contribution();
        $form = $this->createForm(new ContributeMedia(), $contribution);

        $request = $this->getRequest();

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {

                $contribution = $form->getData();
                $em = $this->getDoctrine()->getEntityManager();
                $em->persist($contribution);
                $em->flush();

                $this->sendMail($contribution);

                $this->get('session')
                    ->setFlash('contribution-notice', 'Thank you! Your contribution has been received.');

                return $this->redirect($this->generateUrl('contribute_new'));
            }
        }

        return $this->render('ProtalkMediaBundle:Contribution:new.html.twig', array('form' => $form->createView()));
    }

    private function sendMail($contribution)
    {
        $body = $this->renderView(
            'ProtalkMediaBundle:Contribution:email.txt.twig',
            array('contribution' => $contribution)
        );

        $message = Swift_Message::newInstance()
           ->setSubject('ProTalk - You have new contribution from: ' . $contribution->getEmail())
           ->setFrom('no-reply@protalk.me')
           ->setTo('info@protalk.me')
           ->setBody($body);

        $this->get('mailer')->send($message);
    }
}
