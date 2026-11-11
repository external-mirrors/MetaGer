<?php
return [
    'title' => 'Få ett sökresultat borttaget – MetaGer',
    'meta_description' => 'Så får du ett sökresultat borttaget: rätten att bli bortglömd, föråldrat innehåll, upphovsrätt. Var din begäran fungerar: Google, Brave och andra.',
    'heading' => 'Få ett sökresultat borttaget',
    'lead' => 'MetaGer är en metasökmotor: vi har inget eget index utan visar vad andra sökmotorer hittar. Ett resultat kan därför bara tas bort på riktigt där det kommer ifrån: på webbplatsen och hos de sökmotorer vars resultat MetaGer visar. Den här sidan berättar var.',
    'toc' => [
        'label' => 'På den här sidan',
    ],
    'website' => [
        'heading' => 'Kontakta webbplatsen själv',
        'text' => 'Det är den enda vägen som fungerar överallt. När sidan är raderad eller ändrad tar varje sökmotor bort eller uppdaterar den vid nästa besök, även MetaGer, utan att du behöver fråga var och en.',
        'tips' => [
            'De flesta webbplatser anger en kontaktadress på kontaktsidan eller i integritetspolicyn. Be dem där att radera innehållet, rätta det eller åtminstone ta bort ditt namn.',
            'När det gäller personuppgifter måste webbplatser i EU besvara en begäran enligt art. 17 GDPR (rätten till radering).',
            'Om du själv driver webbplatsen: radera sidan så att den svarar med ”404” eller ”410”, eller märk den med ”noindex”. Sökmotorerna tar då bort den av sig själva.',
        ],
        'sources' => 'För resultat från :sources är detta den enda vägen.',
    ],
    'kind' => [
        'heading' => 'Vad gäller det?',
        'intro' => 'Om webbplatsen inte hjälper, eller om du inte kan vänta på den, har sökmotorerna egna förfaranden. Vilket som passar beror på vad du vill uppnå.',
        'personal_data' => [
            'summary' => 'Personuppgifter: rätten att bli bortglömd',
            'text' => 'Ett resultat för ditt namn leder till uppgifter om dig som är felaktiga, föråldrade, irrelevanta eller överdrivna. Enligt art. 17 GDPR kan du be en sökmotor att sluta visa resultatet vid sökningar på ditt namn. Sökmotorn väger ditt intresse mot allmänhetens intresse av informationen, så alla begäranden går inte igenom. I korten nedan står det under ”Personuppgifter”.',
        ],
        'outdated' => [
            'summary' => 'Föråldrat innehåll',
            'text' => 'Webbplatsen har redan raderat eller ändrat sidan, men resultatet visar fortfarande den gamla titeln eller texten. Det löser sig självt vid sökmotorns nästa besök; hos de flesta kan du också begära en tidigare uppdatering. I korten nedan står det under ”Föråldrat innehåll”.',
        ],
        'legal' => [
            'summary' => 'Upphovsrätt och andra rättsliga skäl',
            'text' => 'Ett resultat gör intrång i din upphovsrätt eller andra rättigheter. Sökmotorer tar emot sådana anmälningar via egna juridiska formulär; oftast kan bara rättighetshavaren eller någon som agerar för denne skicka in dem. I korten nedan står det under ”Upphovsrätt och juridik”.',
        ],
        'illegal' => [
            'summary' => 'Olagligt innehåll',
            'text' => 'Skildringar av sexuella övergrepp mot barn, innehåll som är skadligt för minderåriga, hets mot folkgrupp och liknande: anmäl det först till en rapporteringstjänst. Den agerar mot själva innehållet hos webbhotellet och samarbetar med polisen. Om du dessutom vill att MetaGer slutar visa ett sådant resultat, skicka oss bara adressen (URL) som text i ett mejl. Skicka aldrig själva innehållet till oss, inte heller skärmbilder eller bilagor.',
            'hotlines' => 'Rapporteringstjänster:',
        ],
    ],
    'providers' => [
        'heading' => 'Fråga sökmotorerna som MetaGer visar',
        'text' => 'MetaGer söker inte igenom webben själv. Vi frågar sökmotorerna nedan i samma ögonblick som du söker, visar vad de returnerar och sparar deras svar i cacheminnet i några timmar. Tar någon av dem bort ett resultat saknas det därefter också i det MetaGer får från den.',
        'which' => 'Vilken sökmotor ett resultat kommer från står under resultatet på MetaGers resultatsida. Kommer det från flera, fråga var och en av dem.',
        'shows' => 'På MetaGer under:',
        'kinds' => [
            'personal_data' => 'Personuppgifter',
            'outdated' => 'Föråldrat innehåll',
            'legal' => 'Upphovsrätt och juridik',
        ],
        'no_form' => 'Inget formulär, kontakta :name',
        'contact' => 'Kontakta :name',
        'servers' => 'Mastodon-servrarna som TootNews söker i',
        'google' => 'MetaGer får Googles resultat via tjänsten Serper. Det Google tar bort ur sitt index saknas därefter också på MetaGer.',
        'brave' => 'Brave Search driver ett eget index. Begäranden om personuppgifter skickas med e-post till privacy@brave.com med ämnet ”RTBF request”; den länkade sidan berättar vad mejlet måste innehålla.',
        'mojeek' => 'Mojeek driver ett eget index och redigerar inte sina resultat för hand. Av rättsliga skäl tar Mojeek bort sidor på begäran; skriv via kontaktsidan och ange de exakta adresserna.',
        'staan' => 'Staan är sökgränssnittet från European Search Perspective. Staan tar emot begäranden om personuppgifter via ett eget formulär; fyll i alla fält och bifoga underlagen. Borttagningen gäller bara Staans index.',
        'polias' => 'Polias driver ett eget index och har inget formulär. Skriv till hallo@polias.eu med sidans adress och skälet.',
        'pixabay' => 'Pixabay tillhandahåller bilderna själv. Använd anmälningsformuläret eller anmälningsknappen på bildens sida hos Pixabay.',
        'suma' => 'TootNews drivs av SUMA-EV, föreningen bakom MetaGer, och söker bland inlägg på några få Mastodon-servrar. Vid varje resultat står vilken server det kommer från; be dem som driver servern att radera eller dölja inlägget.',
        'nolm' => 'Nolm är SUMA-EV:s egen sökmotor, skild från MetaGer. Nolm söker i Open Web Index. Begär borttagning med Open Web Index formulär för borttagning: det som tas bort där försvinner också från Nolm.',
        'website' => 'BASE visar vad arkiven som den söker i innehåller. När arkivet tar bort en post försvinner den också från BASE.',
    ],
    'questions' => [
        'text' => 'Har du fler frågor om att ta bort ett sökresultat?',
        'link' => 'Skicka oss ett mejl',
    ],
    'trademarks' => [
        'text' => 'Namnen på andra leverantörers sökmotorer är varumärken som tillhör respektive ägare. Vi använder dem bara för att visa var du kan begära borttagning.',
        'ref' => 'Varumärkesanmärkning',
    ],
];
