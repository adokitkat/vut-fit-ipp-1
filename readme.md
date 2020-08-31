# Implementačná dokumentácia k 1. úlohe do IPP 2019/2020

### Meno a priezvisko: Adam Múdry

### Login: xmudry01

## Analyzátor kódu IPPcode20 - Implementácia

Program **parse.php** príjma vstup na STDIN po riadkoch. Na to, aby súbor spracoval, musí na jeho vrchu byť hlavička ```.IPPcode20```. Ak sa tam nachádza, začína analýza kódu, ktorá funguje pomocou porovnávania stringov v PHP a pomocou regexu. Ak je príjmaný program správny, vygeneruje sa z neho je ho XML reprezentácia. Ak nie je, program sa ukončí s korešpondujúcim chybovým kódom. Na generáciu XML som použil PHP rošírenie XMLWriter.

Na dočasné ukladanie dát používam premennú **$op** s dátovým typom array, ktorá obsahuje všetky potrebné informácie.

Program je rozdelený na tieto hlavné funckie:

- **print_i()** - zapisuje jednotlivé inštrukcie do XML
- **print_arg()** - zapisuje argumenty inštrukcií do XML
- **op()** - rozdeluje string s riadkom na ktorom je inštrukcia a ukladá z neho údaje do premennej $op
- **save_arg()** - ukladá jednotlivé argumenty do premennej $op
- **check_instruction()** - lexikálna a syntaktická analýza inštrukcií

## Použitie

Na zobrazenie pomocnej hlášky spustíme program s ```--help``` vlajkou. Tj. ```parse.php --help```.

Pre načítanie súboru miesto čítania zo STDIN, môžeme použiť ```<```, tj. ```parse.php < program.txt```, kde ```program.txt``` je prožadovaný program na zanalyzovanie.
