<?php

namespace net\splaturn\libcomponentbaseditem;

use Closure;
use muqsit\simplepackethandler\SimplePacketHandler;
use net\splaturn\libcomponentbaseditem\network\EditableItemTypeDictionary;
use pocketmine\data\bedrock\item\SavedItemData;
use pocketmine\item\Apple;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemTypeIds;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\GlobalItemTypeDictionary;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\ItemComponentPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;
use pocketmine\network\mcpe\protocol\types\ItemComponentPacketEntry;
use pocketmine\plugin\Plugin;
use pocketmine\world\format\io\GlobalItemDataHandlers;

class LibComponentBasedItem{

    /** 
     * @var ItemComponentPacketEntry[]
     * @phpstan-var list<ItemComponentPacketEntry> 
     * */
    private array $itemComponentEntries = [];
    private ?ItemComponentPacket $cachedPacket = null;

    public function __construct(Plugin $plugin){
        SimplePacketHandler::createMonitor($plugin)->monitorOutgoing(function(StartGamePacket $pk, NetworkSession $session) : void{
            $session->sendDataPacket($this->getItemComponentPacket());
        });
    }

    public function registerComponentBasedItem(Item $item, string $stringId, CompoundTag $components, Closure $serializerFunc, Closure $deserializerFunc) : void{
        $dictionary = EditableItemTypeDictionary::createWithExistingDictionary(GlobalItemTypeDictionary::getInstance()->getDictionary());
        $networkRuntimeId = $dictionary->addComponentBasedItem($stringId);
        $serializer = GlobalItemDataHandlers::getSerializer();
        $deserializer = GlobalItemDataHandlers::getDeserializer();
        $serializer->map($item, $serializerFunc);
        $deserializer->map($stringId, $deserializerFunc);

        $componentNbt = CompoundTag::create()
            ->setTag("components", $components)
            ->setInt("id", $networkRuntimeId)
            ->setString("name", $stringId);
        $this->itemComponentEntries[] = new ItemComponentPacketEntry($stringId, new CacheableNbt($componentNbt));
        $this->cachedPacket = null;
    }
    
    public function getItemComponentPacket() : ItemComponentPacket{
        if($this->cachedPacket === null){
            $this->cachedPacket = ItemComponentPacket::create($this->itemComponentEntries);
        }

        return $this->cachedPacket;
    }
}