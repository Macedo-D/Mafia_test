<?php

namespace App\Models;

use App\Interfaces\IMafia;
use App\Interfaces\IMember;

class Mafia implements IMafia
{
    private IMember $godfather;
    private array $members = [];

    public function __construct(IMember $godfather){
        $this->godfather = $godfather;
        $this->members[$godfather->getId()] = $godfather;
    }
    public function getGodfather(): IMember {
        return $this->godfather;
    }
    public function addMember(IMember $member): ?IMember {
        $this->members[$member->getId()] = $member;
        return $member;
    }

    public function getMember(int $id): ?IMember {
        return $this->members[$id] ?? null;
    }

    public function sendToPrison(IMember $member): bool {

        $member->setInPrison(true);
        $previousBoss = $member->getBoss();
        $previousBoss->removeSubordinate($member);
        $formerSubordinates = $member->getSubordinates();

        // Transfer subordinates to the oldest remaining boss at the same level
        $newBoss = $this->findAppropriateBoss($previousBoss);
        if(!$newBoss){
            $newBoss = $this->findAppropriateBoss($member);
        }
        foreach ($formerSubordinates as $subordinate) {
            $subordinate->setPreviousBoss($member);
            $subordinate->setBoss($newBoss);
        }
        return true;
    }

    public function releaseFromPrison(IMember $member): bool {

        if ($member->isInPrison()) {
            $member->setInPrison(false);
            $previousBoss = $member->getPreviousBoss();

            foreach ($member->getSubordinates() as $subordinate) {
                // Transfer former subordinates back to the released member
                $actualBoss = $subordinate->getBoss();
                $actualBoss->removeSubordinate($subordinate);

                $subordinate->setBoss($previousBoss);
                $previousBoss->addSubordinate($subordinate);
            }
        }
        return true;
    }
    public function findAppropriateBoss(IMember $member): ?IMember {
        $subordinates = $member->getSubordinates();

        if (!empty($subordinates)) {
            $oldestSubordinate = array_shift($subordinates);

            foreach ($subordinates as $subordinate) {
                if ($subordinate->getAge() > $oldestSubordinate->getAge()) {
                    $oldestSubordinate = $subordinate;
                }
            }
            return $oldestSubordinate;
        }
        return null;
    }
    public function findBigBosses(int $minimumSubordinates): array {

        $bigBosses = [];

        foreach ($this->members as $member) {
            if ($member->getBoss()) {
                $boss = $member->getBoss();
                $subordinateCount = count($boss->getSubordinates());
                if ($subordinateCount > $minimumSubordinates) {
                    $bigBosses[] = $boss;
                }
            }
        }

        return $bigBosses;
    }

    public function compareMembers(IMember $memberA, IMember $memberB): ?IMember {
        $depthA = $this->getDepth($memberA);
        $depthB = $this->getDepth($memberB);

        if ($depthA < $depthB) {
            return $memberB;
        } elseif ($depthB < $depthA) {
            return $memberA;
        }

        return null; // Same level
    }

    public function getDepth(IMember $member):int {
        $depth = 0;
        while ($member->getBoss()) {
            $depth++;
            $member = $member->getBoss();
        }
        return $depth;
    }
}