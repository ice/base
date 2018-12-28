<?php

return [
    // Flash
    "flash/danger/activation" => "<b>Błąd!</b> Aktywacja nie może być zakończona. Nieprawidłowa nazwa użytkownika lub hash.",
    'flash/danger/forbidden' => "<b>Błąd!</b> Nie masz dostępu do tej strony.",
    "flash/notice/activation" => "<b>Informacja!</b> Aktywacja została już zakończona.",
    "flash/notice/checkEmail" => "<b>Informacja!</b> Sprawdź Email w celu aktywacji konta.",
    "flash/success/activation" => "<b>Sukces!</b> Aktywacja zakończona. Zaloguj się.",
    "flash/success/contact" => "<b>Sukces!</b> Wiadomość została wysłana",
    'flash/warning/errors' => '<b>Ostrzeżenie!</b> Proszę poprawić błędy.',
    // App
    "account" => "Konto",
    "activation" => "Aktywacja",
    "adminPanel" => "Panel administratora",
    "close" => "Zamknij",
    "contact" => "Kontakt",
    "content" => "Zawartość",
    "documentation" => "Dokumentacja",
    "email" => "Email",
    "error" => "Błąd",
    "fillFields" => "Uzupełnij pola",
    "fullName" => "Imię i nazwisko",
    "niceDay!" => "Miłego dnia!",
    "hello %s" => "Witaj %s!",
    "hi" => "Cześć",
    "home" => "Główna",
    "iHaveAccount." => "Mam już konto.",
    "lastLogin" => "Ostatnie logowanie",
    "logins" => "logowań",
    "noAccess" => "Brak dostępu",
    "noAccessToPage" => "Nie masz dostępu do tej strony.",
    "noAccount?" => "Nie masz konta?",
    "notFound" => "Nie znaleziono",
    "or" => "albo",
    "password" => "Hasło",
    "rememberMe" => "Pamiętaj mnie",
    "repeatEmail" => "Powtórz email",
    "repeatPassword" => "Powtórz hasło",
    "send" => "Wyślij",
    "sender" => "Nadawca",
    "signIn" => "Zaloguj",
    "signInBy" => "Zaloguj przez",
    "signInToAccess." => "Zaloguj się, aby uzyskać dostęp.",
    "signOut" => "Wyloguj",
    "signUp" => "Zarejestruj",
    "signUpBy" => "Zarejestruj przez",
    "somethingIsWrong" => "Coś jest nie tak!",
    "status :code" => "Status :code",
    "toggle" => "Przełącz",
    "username" => "Nazwa użytkownika",
    // Base
    "baseInfo" => "Bazowa aplikacja napisana w Ice framework",
    "baseStart" => "Użyj tej aplikacji jako sposobu na szybki start każdego nowego projektu.",
    // Email
    "beforeSignIn" => "Zanim będzie można się zalogować, trzeba najpierw aktywować swoje konto.",
    "toActivateClick" => "Aby aktywować konto, kliknij na ten link:",
    // Langs
    "english" => "Angielski",
    "language" => "Język",
    "polish" => "Polski",
    "japanese" => "Japoński",

    // Ice validation
    /** alnum */
    "Field :field must contain only letters and numbers" => "Pole <em>:field</em> może zawierać tylko litery i cyfry",
    /** alpha */
    "Field :field must contain only letters" => "Pole <em>:field</em> może zawierać tylko litery",
    /** between */
    "Field :field must be within the range of :min to :max" => "Pole <em>:field</em> musi zawierać się w przedziale <em>:min</em> do <em>:max</em>",
    /** digit */
    "Field :field must be numeric" => "Pole <em>:field</em> musi być numeryczne",
    /** email */
    "Field :field must be an email address" => "Pole <em>:field</em> musi być adresem e-mail",
    /** in */
    "Field :field must be a part of list: :values" => "Pole <em>:field</em> musi być częścią z listy: <em>:values</em>",
    /** lengthMax */
    "Field :field must not exceed :max characters long" => "Pole <em>:field</em> nie może przekraczać <em>:max</em> znaków",
    /** lengthMin */
    "Field :field must be at least :min characters long" => "Pole <em>:field</em> musi mieć co najmniej <em>:min</em> znaków",
    /** notIn */
    "Field :field must not be a part of list: :values" => "Pole <em>:field</em> nie może być częścią z listy: <em>:values</em>",
    /** regex */
    "Field :field does not match the required format" => "Pole <em>:field</em> nie spełnia wymaganego formatu",
    /** required */
    "Field :field is required" => "Pole <em>:field</em> jest wymagane",
    /** same */
    "Field :field and :other must match" => "Pole <em>:field</em> i <em>:other</em> muszą się zgadzać",
    /** unique */
    "Field :field must be unique" => "Pole <em>:field</em> musi być unikalne",
    /** url */
    "Field :field must be a url" => "Pole <em>:field musi</em> być adresem url",
    /** with */
    "Field :field must occur together with :fields" => "Pole <em>:field</em> musi wystąpić, wraz z <em>:fields</em>",
    /** without */
    "Field :field must not occur together with :fields" => "Pole <em>:field</em> nie może wystąpić, wraz z <em>:fields</em>",
    /* FileEmpty */
    "Field :field must not be empty" => "Pole <em>:field</em> nie może być puste",
    /* FileIniSize */
    "File :field exceeds the maximum file size" => "Plik <em>:field</em> przekracza maksymalny rozmiar pliku",
    /* FileMaxResolution */
    "File :field must not exceed :max resolution" => "Plik <em>:field</em> nie może przekraczać rozdzielczości <em>:max</em>",
    /* FileMinResolution */
    "File :field must be at least :min resolution" => "Plik <em>:field</em> musi być co najmniej rozdzielczości <em>:min</em>",
    /* FileSize */
    "File :field exceeds the size of :max" => "Plik <em>:field</em> przekracza rozmiar <em>:max</em>",
    /* FileType */
    "File :field must be of type: :types" => "Plik <em>:field</em> musi być typu: <em>:types</em>",
    /* FileValid / default */
    "Field :field is not valid" => "Pole <em>:field</em> jest nieprawidłowe",
];
