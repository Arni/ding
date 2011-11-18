** INSTALLATION **

1) Kontakt Place2book (www.place2book.com) og f� oprettet konti (test-konto og driftskonto)
2) N�r kontoen er oprettet, kopieres API-key fra siden https://www.place2book.com/da/event_makers#tab4 
3) Indstil modulet i ding p� siden "Place2book settings" ( /admin/settings/ding/place2book )


** DOKUMENTATION **

PLACE2BOOK:
Modulet kobler op mod Place2books API. L�s mere om denne API p� https://github.com/place2book/place2book-api/wiki

INDSTILLINGER i D!NG (siden /admin/settings/ding/place2book):
- "Place2Book service URL" skal pege p� Place2books Event API. URLen er https://www.place2book.com/da/event_api
- "Place2Book base API key" indeholder den generelle API n�gle n�r der forbindes til Place2book. Hvis du angiver API n�gler for et specifikt bibliotek (nedenfor), vil events relateret til det bibliotek bruge d�n API n�gle i stedet for den generelle API n�gle. 
- API key {BIBLIOTEKSNAVN}. Disse vil v�re forskellige i antal fra installation til installation. Hvert bibliotek oprettet i ding kan tilknyttes sin egen konto hos Place2book. I det tilf�lde tilsides�ttes den generelle API n�gle til fordel for den angive n�gle n�r der sendes data p� events relateret til d�t bibliotek.
- Place2book-standardv�rdier (Kapacitet, Vedligehold kopi, Kultunaut Export). Disse felter findes p� hver event oprettet i ding. N�r nye events oprettes, er v�rdierne herfra allerede indsat i det event man er ved at oprette.

KULTUNAUT:
Modulet sender ogs� de data til Place2book, som bruges n�r Place2book videresender oplysninger om arrangementet til Kultunaut. Som billede til Kultunaut bruges listevisningsbilledet fra indholdet i ding. Som emneord til Kultunaut medsendes eventets emneord fra Event Target og Event Category. Disse svarer typisk ikke til de emneord, der bruges p� Kultunaut, men Kultunaut kan bruge disse til at placere arrangementet rigtigt. Til version 1.1 af ding_place2book planl�gges indstillinger, hvormed man kan overs�tte emneordene i ding til kultunat emneord, s�ledes at Kultunaut f�r de �nskede emneord fra starten. 


** VERSIONSINFORMATION **

v1.0.1: Rettelse der lukker sikkerhedshul, hvor alle brugertyper kunne tilg� indstillingerne (siden /admin/settings/ding/place2book). Ved opgradering fra version 1.0.0, skal update.php k�res, eller cachen t�mmes. 


** TIPS **

1) Man kan have flere konti hos Place2book. Der er i ding_place2book mulighed for at angive en separat API-key pr. bibliotek.

2) Eksempel p� en SQL der kan k�res p� en eksisterende ding-hjemmeside for at tr�kke indhold ud der har et link i br�dteksten til Place2book. Vi har brugt den i Vejle til at generere ding_place2book tabellen, som anvendes af modulet. Vi kunne derved lave en mere glidende overgang da modulet blev taget i brug p� vores driftssite og eksisterende driftskonto hos Place2book:

SELECT r.nid, substring(r.body, (LOCATE('place2book.com/event/', r.body)+21), (LOCATE('">Bestil billet', r.body) - (LOCATE('place2book.com/event/', r.body)) -21 ) ) AS place2book_id, 1 AS maintain_copy, 0 AS kultunaut_export FROM {node} n JOIN {node_revisions} r ON n.nid=r.nid WHERE n.type = 'event' AND r.body LIKE '%place2book.com/event/%';

Derefter var det dog n�dvendigt manuelt at rette kapacitet til p� alle events, der ikke havde ubegr�nset adgang - ellers ville ding_place2book have overskrevet disse v�rdier p� Place2book-kontoen.

