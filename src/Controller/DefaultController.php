<?php

/*
 * Copyright 2016-2018 Mathieu Piot.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage", methods={"GET"})
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        if (null !== $this->getUser()) {
            $userGroups = $em->getRepository('App:Group')->findAllForUser($this->getUser());
        } else {
            $userGroups = null;
        }

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'groups' => $userGroups,
        ]);
    }

    /**
     * @Route("/faq", name="faq", methods={"GET"})
     */
    public function faqAction()
    {
        return $this->render('default/faq.html.twig');
    }
}
