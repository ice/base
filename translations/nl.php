<?php

return [
    // Flash
    "flash/danger/activation" => "<b>Let op!</b> De activatie is mislukt. Dit is een ongeldige gebruikersnaam of activatiecode.",
    'flash/danger/forbidden' => "<b>Oh oh!</b> Je hebt geen toegang tot deze pagina.",
    "flash/notice/activation" => "<b>OK</b> De activatie is voltooid.",
    "flash/notice/checkEmail" => "Controleer je e-mail om het account te activeren.",
    "flash/success/activation" => "<b>OK!</b> De activatie is voltooid. Je kunt nu inloggen.",
    "flash/success/contact" => "<b>OK!!</b> Het bericht is verstuurd",
    'flash/warning/errors' => '<b>Er ging iets mis.</b> Los de problemen eerst op.',
    // App
    "account" => "Account",
    "activation" => "activatie",
    "adminPanel" => "Websitebeheer",
    "close" => "Sluiten",
    "contact" => "Contact",
    "content" => "Inhoud",
    "documentation" => "Documentatie",
    "email" => "Email",
    "error" => "Foutmelding",
    "fillFields" => "Vul de velden in",
    "fullName" => "Volledige naam",
    "niceDay!" => "Een fijne dag!",
    "hello %s" => "Hallo %s.",
    "hi" => "Hoi",
    "home" => "Home",
    "iHaveAccount." => "Ik heb al een account.",
    "lastLogin" => "Last login",
    "logins" => "Logins",
    "noAccess" => "Geen toegang",
    "noAccessToPage" => "Je hebt geen toegang tot deze pagina.",
    "noAccount?" => "Heb je nog geen account?",
    "notFound" => "Niet gevonden",
    "or" => "of",
    "password" => "Wachtwoord",
    "rememberMe" => "Wachtwoord onthouden",
    "repeatEmail" => "Herhaal e-mail",
    "repeatPassword" => "Herhaal wachtwoord",
    "send" => "Versturen",
    "sender" => "Verzender",
    "signIn" => "Inloggen",
    "signInBy" => "Sign in by",
    "signInToAccess" => "Om deze pagina te zien moet je zijn ingelogd.",
    "signOut" => "Sign out",
    "signUp" => "Sign up",
    "signUpBy" => "Sign up by",
    "somethingIsWrong" => "Er ging iets verkeerd!",
    "status :code" => "Status :code",
    "toggle" => "Toggle",
    "username" => "Gebruikersnaam",
    // Base
    "baseInfo" => 'Deze applicatie is ontwikkeld met het Ice Framework.',
    "baseStart" => "Gebruik deze applicatie om snel te kunnen starten met je nieuwe project.",
    // Email
    "beforeSignIn" => "Activeer eerst je account om in te loggen.",
    "toActivateClick" => "Klik op de link om je account te activeren:",
    // Langs
    "english" => "English",
    "language" => "Language",
    "polish" => "Polish",
    "dutch" => "Nederlands",
    "japanese" => "Japanese",

    // Ice validation
    /** alnum */

    /** alnum */
    "Het veld :field mag uitsluitend letters en cijfers bevatten" => "Field <em>:field</em> must contain only letters and numbers",
    /** alpha */
    "Het veld :field mag uitsluitend letters bevatten" => "Field <em>:field</em> must contain only letters",
    /** between */
    "Het veld :field moet groter of gelijk zijn aan :min en kleiner of gelijk aan :max" => "Field <em>:field</em> must be within the range of <em>:min</em> to <em>:max</em>",
    /** digit */
    "Het veld :field moet een numerieke waarde bevatten" => "Field <em>:field</em> must be numeric",
    /** email */
    "Het veld :field moet een geldig e-mail adres bevatten." => "Field <em>:field</em> must be an email address",


    "Field :field must contain only letters and numbers" => "Het veld <em>:field</em> mag uitsluitend letters en cijfers bevatten",
    /** alpha */
    "Field :field must contain only letters" => "Het veld <em>:field</em> mag uitsluitend letters bevatten",
    /** between */
    "Field :field must be within the range of :min to :max" => "Het veld <em>:field</em> moet groter zijn of gelijk aan <em>:min</em> en kleiner of gelijk aan <em>:max</em>",
    /** digit */
    "Field :field must be numeric" => "Het veld <em>:field</em> moet een numerieke waarde bevatten",
    /** email */
    "Field :field must be an email address" => "Het veld <em>:field</em> moet een geldig e-mail adres gevatten",
    /** in */
    "Field :field must be a part of list: :values" => "Het veld <em>:field</em> moet een van de volgende waardes bevatten: <em>:values</em>",
    /** lengthMax */
    "Field :field must not exceed :max characters long" => "Het veld <em>:field</em> mag niet meer dan <em>:max</em> tekens bevatten",
    /** lengthMin */
    "Field :field must be at least :min characters long" => "Het veld <em>:field</em> moet tenminste <em>:min</em> tekens bevatten",
    /** notIn */
    "Field :field must not be a part of list: :values" => "Het veld <em>:field</em> mag niet hetzelfde zijn als een van de volgende waardes: <em>:values</em>",
    /** regex */
    "Field :field does not match the required format" => "Het veld <em>:field</em> bevat een waarde in een ongeldig formaat.",
    /** required */
    "Field :field is required" => "Het veld <em>:field</em> is verplicht",
    /** same */
    "Field :field and :other must match" => "Het veld <em>:field</em> en <em>:other</em> moeten overeenkomen",
    /** unique */
    "Field :field must be unique" => "Het veld <em>:field</em> moet een unieke waarde bevatten",
    /** url */
    "Field :field must be a url" => "Het veld <em>:field</em> moet een geldige URL bevatten.",
    /** with */
    "Field :field must occur together with :fields" => "Het veld <em>:field</em> moet gebruikt worden in combinatie met de volgende velden: <em>:fields</em>",
    /** without */
    "Field :field must not occur together with :fields" => "Het veld <em>:field</em> mag niet gebruikt in combinatie met de volgende velden:  <em>:fields</em>",
    /* FileEmpty */
    "Field :field must not be empty" => "Het veld <em>:field</em> mag niet leeg zijn",
    /* FileIniSize */
    "File :field exceeds the maximum file size" => "Het bestand <em>:field</em> is overschrijdt de maximale bestandsgrootte",
    /* FileMaxResolution */
    "File :field must not exceed :max resolution" => "De resolutie van het bestand  <em>:field</em> mag niet groter zijn dan <em>:max</em>",
    /* FileMinResolution */
    "File :field must be at least :min resolution" => "De resolutie van het bestand  <em>:field</em> mag niet kleiner zijn dan <em>:min</em>",
    /* FileSize */
    "File :field exceeds the size of :max" => "Het bestand  <em>:field</em> overschrijdt de maximum grootte van <em>:max</em>",
    /* FileType */
    "File :field must be of type: :types" => "Het bestand  <em>:field</em> is ongeldig. De volgende bestandtypes zijn toegestaan: <em>:types</em>",
    /* FileValid / default */
    "Field :field is not valid" => "Het veld <em>:field</em> bevat een ongeldige waarde",
];
