# Fio Signer (PHP implementace aplikace Fio podpis)

Knihovna umožňuje automatizovaně podepisovat pokyny pro autorizaci platebních příkazů, změny příkazů, změny na
platebních kartách a dalších nastavení pro které je nutná autorizace v internetovém
bankovnictví Fio banky.

Díky této knihovně je tedy možné například autorizovat či zamítnout platební příkazy nebo příkazy z dávky, které byly dříve zaslané do banky pomocí [Fio API](https://www.fio.cz/docs/cz/API_Bankovnictvi.pdf).
Ve spolupráci s Fio API je tedy možné plně automatizovat zadávání a autorizaci platebních příkazů z vlastního účtu vedeného u Fio banky.

Jedná se o alternativní implementaci GUI aplikace [Fio podpis](https://www.fio.cz/docs/cz/fio_podepisovac.pdf) a používá se zde stejného komunikačního protokolu se servery Fio banky.

## Instalace
Nejlepší cestou, jak Fio Signer nainstalovat je pomocí [Composeru](http://getcomposer.org/):

```
> composer require obud/fiosigner
```

## Než se začne používat

### Generování privátního a veřejného klíče
Pro přístup ke speciálnímu API podepisovače Fio banky je nutné nejprve vygenerovat privátní a veřejný klíč. K tomu slouží utilita `keygen`, která se postará o vygenerování těchto klíčů ve správném formátu.

Z konzole tedy zavoláme:
```
> ./bin/keygen {username} {passphrase}
```
Řetězec `{username}` nahradíme Vaším přihlašovacím jménem do internet bankingu Fio a `{passphrase}` nahradíme heslem, kterým bude zaheslován nově vygenerovaný privátní klíč.

Do pracovního adresáře se uloží tři soubory:
- `private.pem` - zaheslovaný privátní klíč - budeme potřebovat pro funkčnost knihovny
- `public.pem` - veřejný klíč - budeme potřebovat pro funkčnost knihovny
- `{username}_Y-m-d_H-i.xml.pub` - veřejný klíč ve speciálním formátu pro Fio banku (viz dále)


### Předání veřejného klíče Fio bance
Jediným způsobem, jak nahrát Váš veřejný klíč do Fio banky je ten, že výše vygenerovaný soubor `{username}_Y-m-d_H-i.xml.pub` uložíme na **USB flash disk** a osobně jej doneseme na libovolnou pobočku Fio banky.
Na pobočce požádáme o „**nahrání klíče pro elektronický podpis**“. Pro tento úkon je většinou na pobočce vyškolen pouze jeden zaměstnanec, bude to tedy vyžadovat jistou dávku trpělivosti.

Po ztotožnění předložíme **USB flash disk**, ze kterého si pracovník stáhne Váš veřejný klíč. Zároveň žádáme o nastavení volby bezpečnostního prvku na možnost „**volitelně elektronickým podpisem NEBO sms autorizačním kódem**“ *(za SMS banka považuje i mobilní aplikaci)*. Zaškrtnutí této volby si **následně zkontrolujeme i při podpisu Protokolu o nastavení autorizace elektronických pokynů**.

Od této chvíle by mělo být možné se autentizovat klíčem na endpointech Fio podepisovače.

## Použití
Níže je k dispozici sebepopisující se ukázka použití Fio Signeru.

Je třeba brát v úvahu, že podepisovací server vrátí vždy jen jeden pokyn nebo jednu dávku k autorizaci. Pokud se tedy vrácený pokyn nebo dávka neodbaví, bude blokovat případné další pokyny k podpisu, které zůstanou čekat ve frontě na serveru.

Dále je potřeba myslet na to, že pokyny může odbavovat také člověk v internet bankingu pomocí SMS nebo s mobilní aplikací. Pokyny, které nechceme automatizovaně podepisovat, ani zamítat, tedy můžeme nechat na vyřízení člověka - nicméně zpracování ostatních pokynů ve frontě bude tímto blokováno. Pokud bychom bezhlavě všechny nevyhovující pokyny zamítali, může docházet ke znemožnění podepsat jakýkoli pokyn ručně pomocí SMS nebo aplikace (pokyn bude automatizovaně zamítnut ještě před tím, než jej člověk stihne ručně schválit).

Jako dávku považujeme pokyn, který v internet bankingu autorizuje více platebních příkazů. U Fio Signeru je i v případě dávky každý jednotlivý příkaz potřeba zpracovat jednotlivě, jsou proto z dávky extrahovány a pro naše účely se chovají jako jednotlivé pokyny. Příkazy v dávce jsou načteny všechny hromadně a je možné je zpracovávat v libovolném pořadí, či zpracovat jen některé a ostatní nechat na ručním vyřízení.

```php
use Obud\FioSigner\FioSigner;
use Obud\FioSigner\Api\Response\Order;
use Obud\FioSigner\Connection\ConnectionService;
use Obud\FioSigner\Crypto\CryptoService;


// Naváže TLS spojení se serverem 'podepisovac1.fio.cz'
$connection = new ConnectionService();

// Připraví klíče pro podpisy
$crypto = new CryptoService(
    __DIR__ . '/private.pem',
    __DIR__ . '/public.pem',
    '{passphrase}',
);

// Založí sezení a autentizuje se (je možné zavolat pouze jednou v rámci spojení)
$fioSigner = new FioSigner(
    '{username}',
    $crypto,
    $connection,
);

// Stáhne seznam pokynů k autorizaci (v rámci sezení lze volat opakovaně pro získání dalších pokynů či dávek z fronty)
$orders = $fioSigner->getOrders();
foreach ($orders as $order) {
    /** @var Order $order */
    if (
        $order->getType() === 'Jednor.plat.příkaz' &&
        $order->getSourceAccount() === '1234567890' &&
        $order->getDestinationAccount() === '16515456' &&
        $order->getDestinationBank() === '0300' &&
        (float)$order->getAmount() < 1000
    ) {
        // Potvrdí (podepíše) pokyn
        $fioSigner->sign($order);
    }

    if (
        $order->getType() === 'Jednor.plat.příkaz' &&
        $order->getSourceAccount() === '1234567890' &&
        $order->getDestinationAccount() === '5454651561' &&
        $order->getDestinationBank() === '2010'
    ) {
        // Potvrdí (podepíše) pokyn
        $fioSigner->sign($order);
    }

    if ($order->getType() === 'Zobrazení tokenů v Internetbankingu') {
        // Zamítne pokyn
        $fioSigner->discard($order);
    }
}

// Ukončí TLS spojení
$connection->disconnect();
```