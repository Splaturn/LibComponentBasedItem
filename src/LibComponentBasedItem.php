<?php

namespace net\splaturn\libcomponentbaseditem;

use net\splaturn\libcomponentbaseditem\network\EditableItemTypeDictionary;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class LibComponentBasedItem{

    /** 
     * @var ItemComponentPacketEntry[]
     * @phpstan-var list<ItemComponentPacketEntry> 
     * */
    private array $itemComponentEntries = [];
    private ItemComponentPacket $cachedPacket;

    private EditableItemTypeDictionary $dictionary;

    public function __construct(){
        $this->dictionary = EditableItemTypeDictionary::createWithExistingDictionary(GlobalItemTypeDictionary::getInstance()->getDictionary());
    }

    public function registerComponentBasedItem(Item $item, string $stringId, CompoundTag $components) : void{
        $networkRuntimeId = $this->dictionary->addComponentBasedItem($stringId);
        $serializer = GlobalItemDataHandlers::getSerializer();
        $deserializer = GlobalItemDataHandlers::getDeserializer();
        $serializer->map($item, fn() => new SavedItemData($stringId));
        $deserializer->map($stringId, fn() => $item);

        $componentNbt = CompoundTag::create()
            ->setTag("components", $components)
            ->setInt("id", $networkRuntimeId)
            ->setString("name", $stringId);
        $this->itemComponentEntries[] = new ItemComponentPacketEntry($stringId, new CacheableNbt($componentNbt));

    }

    public function build() : void{
        $this->cachedPacket = ItemComponentPacket::create($this->itemComponentEntries);
        GlobalItemTypeDictionary::setInstance(new GlobalItemTypeDictionary($this->dictionary->build()));
    }

    public function getItemComponentPacket() : ItemComponentPacket{
        return $this->cachedPacket;
    }
}