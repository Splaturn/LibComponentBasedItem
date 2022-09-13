# LibComponentBasedItem
このライブラリはpm5上でdata driven itemを実装するのを手助けするためのものです。

# 使い方
```php
use net\splaturn\libcomponentbaseditem\LibComponentBasedItem;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\network\mcpe\protocol\BiomeDefinitionListPacket;
use pocketmine\network\mcpe\protocol\StartGamePacket;
use pocketmine\network\mcpe\protocol\types\Experiments;
use pocketmine\plugin\Plugin;

/** 
 * @var Item $chocolate
 * @var CompoundTag $componentsNBT
 * @var Plugin $plugin
 */
$lib = new LibComponentBasedItem();
$lib->registerComponentBasedItem($chocolate, "splaturn:chocolate", $componentsNBT);
$lib->build();

/**
 * Example Code
 * Experimentsでdata_driven_itemを有効化するのと
 * BiomeDefinitionListPacketの後にItemComponentPacketが送信できるのであれば
 * その方法でよいです。
 */
SimplePacketHandler::createInterceptor($plugin)
->interceptOutgoing(function(StartGamePacket $pk, NetworkSession $session) : void{
        $pk->levelSettings->experiments = new Experiments([
            'data_driven_item' => true
        ], true);
    })
    ->interceptOutgoing(function(BiomeDefinitionListPacket $pk, NetworkSession $session) use($lib) : void{
        $session->sendDataPacket($lib->getItemComponentPacket());
    });
