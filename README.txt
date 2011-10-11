** INSTALLATION **

1) Kontakt Place2book (www.place2book.com) og f� oprettet konti (test-konto og driftskonto)
2) N�r kontoen er oprettet, kopieres API-key fra siden https://www.place2book.com/da/event_makers#tab4 
3) Indstil modulet i ding p� siden "Place2book settings" ( /admin/settings/ding/place2book )

** DOKUMENTATION **

Modulet kobler op mod Place2books API. L�s mere om denne API p� https://github.com/place2book/place2book-api/wiki

** TIPS **

1) Man kan have flere konti hos Place2book. Der er i ding_place2book mulighed for at angive en separat API-key pr. bibliotek.

2) Eksempel p� en SQL der kan k�res p� en eksisterende ding-hjemmeside for at tr�kke indhold ud der har et link i br�dteksten til Place2book. Vi har brugt den i Vejle til at generere ding_place2book tabellen, som anvendes af modulet. Vi kunne derved lave en mere glidende overgang da modulet blev taget i brug p� vores driftssite og eksisterende driftskonto hos Place2book:

SELECT r.nid, substring(r.body, (LOCATE('place2book.com/event/', r.body)+21), (LOCATE('">Bestil billet', r.body) - (LOCATE('place2book.com/event/', r.body)) -21 ) ) AS place2book_id, 1 AS maintain_copy, 0 AS kultunaut_export FROM {node} n JOIN {node_revisions} r ON n.nid=r.nid WHERE n.type = 'event' AND r.body LIKE '%place2book.com/event/%';

Derefter var det dog n�dvendigt manuelt at rette kapacitet til p� alle events, der ikke havde ubegr�nset adgang - ellers ville ding_place2book have overskrevet disse v�rdier p� Place2book-kontoen.

