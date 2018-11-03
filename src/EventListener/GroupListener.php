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

namespace App\EventListener;

use App\Entity\Group;
use Doctrine\ORM\Event\OnFlushEventArgs;

class GroupListener
{
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        // When user create a new Group
        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if (!$entity instanceof Group) {
                return;
            }

            $group = $this->addAsMemberGroupAdministrators($entity);

            $metaData = $em->getClassMetadata('App\Entity\Group');
            $uow->computeChangeSet($metaData, $group);
        }

        // When user edit an existant Group
        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            if (!$entity instanceof Group) {
                return;
            }

            $group = $this->addAsMemberGroupAdministrators($entity);

            $metaData = $em->getClassMetadata('App\Entity\Group');
            $uow->computeChangeSet($metaData, $group);
        }
    }

    private function addAsMemberGroupAdministrators(Group $group)
    {
        foreach ($group->getAdministrators()->toArray() as $administrator) {
            if (!$group->getMembers()->contains($administrator)) {
                $group->addMember($administrator);
            }
        }

        return $group;
    }
}
