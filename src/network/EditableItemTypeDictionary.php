<?php

namespace net\splaturn\libcomponentbaseditem\network;

use InvalidArgumentException;
use pocketmine\network\mcpe\protocol\serializer\ItemTypeDictionary;
use pocketmine\network\mcpe\protocol\types\ItemTypeEntry;

class EditableItemTypeDictionary{

    private array $itemTypes = [];
    private array $occupiedStringIds = [];

    private int $nextRuntimeId = PHP_INT_MIN;
    
    public static function createWithExistingDictionary(ItemTypeDictionary $dictionary) : self{
        $editableDictionary = new self;
        foreach($dictionary->getEntries() as $entry){
            $editableDictionary->addAlreadyRegisteredItem($entry);
        }
        return $editableDictionary;
    }

    private function addAlreadyRegisteredItem(ItemTypeEntry $entry) : void{
        if($entry->getNumericId() >= $this->nextRuntimeId){
            $this->nextRuntimeId = $entry->getNumericId() + 1;
        }
        $this->internalAddItem($entry);
    }

    private function internalAddItem(ItemTypeEntry $entry) : void{
        $this->occupiedStringIds[$entry->getStringId()] = $entry;
        $this->itemTypes[] = $entry;
    }

    private function getNextRuntimeId() : int{
        return $this->nextRuntimeId++;
    }

    /**
     * @return int RuntimeId
     */
    public function addComponentBasedItem(string $stringId) : int{
        if(isset($this->occupiedStringIds[$stringId])){
            throw new InvalidArgumentException("given string id: {$stringId} is already occupied before. make sure there is no conflict in item string id.");
        }
        $runtimeId = $this->getNextRuntimeId();
        $entry = new ItemTypeEntry($stringId, $runtimeId, true);
        $this->internalAddItem($entry);
        return $runtimeId;
    }

    public function build() : ItemTypeDictionary{
        return new ItemTypeDictionary($this->itemTypes);
    }
}