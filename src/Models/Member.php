<?php
namespace App\Models;

use App\Interfaces\IMember;

class Member implements IMember {
    private int $id;
    private int $age;
    private ?IMember $boss;
    private ?IMember $previousBoss;
    private array $subordinates = [];
    private bool $inPrison = false;

    public function __construct(int $id, int $age) {
        $this->id = $id;
        $this->age = $age;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getAge(): int {
        return $this->age;
    }

    public function addSubordinate(IMember $subordinate): IMember {
        $this->subordinates[] = $subordinate;
        return $this;
    }

    public function removeSubordinate(IMember $subordinate): ?IMember {
        $key = array_search($subordinate, $this->subordinates);
        if ($key !== false) {
            unset($this->subordinates[$key]);
        }
        return $this;
    }

    public function getSubordinates(): array {
        return $this->subordinates;
    }

    public function getBoss(): ?IMember {
        return $this->getId()!=1?$this->boss:$this;
    }

    public function setBoss(?IMember $boss): IMember {
        $this->boss = $boss;
        $boss? $boss->addSubordinate($this):null;
        return $this;
    }

    public function getPreviousBoss(): ?IMember {
        return $this->previousBoss;
    }

    public function setPreviousBoss(?IMember $boss): IMember {
        $this->previousBoss = $boss;
        return $this;
    }

    public function setInPrison(bool $inOut): IMember {
        $this->inPrison = $inOut;
        return $this;
    }

    public function isInPrison(): bool {
        return $this->inPrison;
    }
}
