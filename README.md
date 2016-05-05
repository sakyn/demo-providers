DEMO - FOOD DELIVERY!
====================


> **Poznámka:**

> Repozitář je pouze demonstrací řešení a není určen k produkčnímu použití.

----------


Teoretická část:
-------------

Zadání uvádí, že každých 5 minut přijde na endpoint mikroslužby 100k produktů a pracovat se bude s daty za poslendích 16 týdnů. Jelikož je při návrhu vhodné počítat s nejhorším možným scénářem, budu tedy dále předpokládat, že může celá dávka obsahovat vždy i pouze nové produkty, přestože to tak samozřejmě spíše nebude.

Za 16 týdnů jde tedy o cca 3,2mld produktů. Plovoucí agregace za posledních 30 dní tedy může obsahovat téměř 90M záznamů. Pro efektivní zpracování bych tedy volil cestu spíše přičítáním inkrementu k aktuálnímu týdnu pro grafy a aktuálnímu dni pro plovoucí průměr namísto ukádání celé historie, se kterou se dále stejně nepracuje. Running 30 day average bych řešil seznamem cen za posledních 30 dní, případně rovnou vytvořit stack 1-30 a zapisovat do něj podle pořadí aktuálního dne. Když už bude tento stack v jedné struktuře, počítal bych průměr rovnou při zápisu.

Pro toto řešení si myslím že není například MySQL příliš vhodná, update hodnot bude celkem zdržovat obzvláště při použití replikace (MSSQL má alespoň fci LEAD která lze využít pro mazání starých hodnot), daleko vhodnější se mi jeví využít [HBase](https://hbase.apache.org/), nebo [Redis](http://redis.io/), který má struktury právě na ty stacky pro inkrementální počty. Vytažení produktu s nějvětší změnou by tak bylo možné dle sloupce, kde je ta delta počítaná s limitem 1, případně počítat dle nové ceny již při updatu a držet si tento údaj staticky.

Nabízí se také využít [bulk api ElasticSearch](https://www.elastic.co/guide/en/elasticsearch/reference/current/docs-bulk.html) a agregovat data z replogu. Při takové frekvenci získávání poměrně velkého množství dat bych rozhodně nespoléhal, že worker stihne data zpracovat než přijde další dávka a určitě využil nějaké message queue, třeba [RabbitMQ](https://www.rabbitmq.com/). Získám tím přehled o velikosti fronty a můžu dynamicky přidělovat workery, které budou zpracovávat menší dávky 100-1000 produktů. Díky tomu bude aplikace daleko lépe škálovatelná a případně zvýšení velikosti dávky, či zvýšení frekvence updatu vyřeším zvýšením počtu zpracovávajících workerů a nebudu zdržovat příjem dávky na endpointu mikroslužby.

Výodou využití RabbitMQ/ZeroMQ,.. je také to, že se minimalizuje čas, kdy jsou data držena pouze v paměti a mohl bych o ně při výpadku přijít. Také lze například při údržbě databáze zápis pozdržet.

Pokud bych měl zmínit i mnou netestované technologie, myslím si, že by stála za otestování kombinace [Apache Spark](https://spark.apache.org/) + [Hive](https://hive.apache.org/), který je na tento workflow určený. Zajímavou možností by bylo využití také nějaké time-series databáze, která slouží právě pro uchovávání hodnot v čase. Já osobně zkouším využití [InfluxDB](https://influxdata.com/time-series-platform/influxdb/), která se jeví v aktuální verzi velmi spolehlivá, avšak nejsem si jist, jestli je již čas pro produkční nasazení. Vím ale, že třeba SocialBackers s Influx také testují a nyní snad i zvažují nasazení. Kromě bleskového zápisu, lze totiž využít Continuous Queries, které mi dokáží v reálném čase data agregovat na statické hodnoty v samostatných záznamech.


----------


Praktická část
-------------------

Aplikace získá pomocí formuláře adresu uživatele a má ověřit, kteří prodejci na danou adresu dovážejí. V životním cyklu aplikace se pak při validaci formuláře kontaktuje [Google Geocoding API](https://developers.google.com/maps/documentation/geocoding/intro#BYB) a z pole address_components se získá normalizovaná adresa. JSON tohoto pole se pak použije pro dotaz na API k získání dodavatelů. Třídy komunikujícíc s Geocoding API a API dodavatelů mají společného předka, využívajícího klienta Guzzle. Původně využívaná knihovna [Kdyby/Curl](https://github.com/Kdyby/Curl) již není vyvíjena, lze ale samozřejmě nahradit běžným Curl requestem, samozřejmě s odpovídajícím ošetřením chyb.

Aplikace získaná data žádným způsobem nedrží, v produkci by bylo vhodné samozřejmě response ukládat pro omezení počtu požadavků na API. Primárně pak údaje z geocoding API jsou prakticky neměnné, totéž platí částečně o číselníku providerů. V demo aplikaci by asi postačilo využít Nette Cache pro výsledek volání metody.

V reálné apliakci by pak bylo vhodné zajistit uživatelskou přívětivost a třeba pomocí [WebSockets](http://socket.io/) zobrazovat průběh zpracování, za minimální rozšíření bych pak považoval využít alespoň Nette Ajax knihovnu a místo refreshe po odeslání formuláře použe překreslit snippet s flash message a seznamem dodavatelů.

DEMO: [http://attdemo.sakac.cz](http://attdemo.sakac.cz)
