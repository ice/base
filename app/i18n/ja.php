<?php

return [
    // Flash
    "flash/danger/activation" => "ユーザ名 または パスワードが正しくありません。",
    'flash/danger/forbidden' => "このページはアクセスできません。",
    "flash/notice/activation" => "アカウントは使用できる状態になりました。",
    "flash/notice/checkEmail" => "アカウントを有効にするためにメールをご確認ください。",
    "flash/success/activation" => "アカウントは使用できる状態になりました。ログインしてください。",
    "flash/success/contact" => "メッセージを送信しました。",
    "flash/warning/errors" => "入力内容に誤りがあります。修正してください。",
    // App
    "account" => "アカウント",
    "activation" => "アクティベーション",
    "adminPanel" => "管理パネル",
    "close" => "閉じる",
    "contact" => "お問合せ",
    "content" => "内容",
    "documentation" => "ドキュメント",
    "email" => "メール",
    "error" => "エラー",
    "fillFields" => "入力欄を埋めてください。",
    "fullName" => "お名前",
    "niceDay!" => "よい1日を!",
    "hello %s" => "こんにちは %s さん。",
    "hi" => "こんにちは",
    "home" => "ホーム",
    "iHaveAccount." => "既に会員登録済の方はこちら",
    "lastLogin" => "最終ログイン",
    "logins" => "ログイン回数",
    "noAccess" => "アクセス権無し",
    "noAccessToPage" => "このページへのアクセス権がありません。",
    "noAccount?" => "アカウントを取得していないのですか？",
    "notFound" => "ページが見つかりません",
    "or" => "または",
    "password" => "パスワード",
    "rememberMe" => "ログインしたままにする。",
    "repeatEmail" => "メール（再入力）",
    "repeatPassword" => "パスワード（再入力）",
    "send" => "送信",
    "sender" => "送信元",
    "signIn" => "ログイン",
    "signInBy" => "ソーシャルアカウントでログイン",
    "signInToAccess" => "アクセスするためにログインしてください。",
    "signOut" => "ログアウト",
    "signUp" => "会員登録",
    "signUpBy" => "ソーシャルアカウントで会員登録",
    "somethingIsWrong" => "誤りがあります!",
    "status :code" => "ステータス :code",
    "toggle" => "切替",
    "username" => "ユーザ名",
    // Base
    "baseInfo" => 'ice framework で作られた基本的なアプリケーション。',
    "baseStart" => "新しいプロジェクトを素早く立ち上げるためにこのアプリケーションをご利用ください。",
    // Email
    "beforeSignIn" => "ログインするために、まずアカウントを使用できる状態にする必要があります。",
    "toActivateClick" => "アカウントを有効にするために, このリンクをクリックしてください。",
    // Langs
    "english" => "英語",
    "language" => "言語",
    "polish" => "ポーランド語",
    "japanese" => "日本語",

    // Ice validation
    /** alnum */
    "Field :field must contain only letters and numbers" => ":field 欄は半角英数字のみで入力してください。",
    /** alpha */
    "Field :field must contain only letters" => ":field 欄は半角アルファベットのみで入力してください。",
    /** between */
    "Field :field must be within the range of :min to :max" => ":field 欄は :min から :max の範囲で入力してください。",
    /** digit */
    "Field :field must be numeric" => ":field 欄は半角数字を入力してください。",
    /** default */
    "Field :field is not valid" => ":field 欄の入力は無効です。",
    /** email */
    "Field :field must be an email address" => ":field 欄は正しいメールアドレスの形式で入力してください。",
    /** in */
    "Field :field must be a part of list: :values" => ":field 欄の選択された値は正しくありません。",
    /** lengthMax */
    "Field :field must not exceed :max characters long" => ":field 欄は :max 文字以内で入力してください。",
    /** lengthMin */
    "Field :field must be at least :min characters long" => ":field 欄は :min 文字以上で入力してください。",
    /** notIn */
    "Field :field must not be a part of list: :values" => ":field 欄の選択された値は正しくありません。",
    /** regex */
    "Field :field does not match the required format" => ":field 欄は正しい形式で入力してください。",
    /** required */
    "Field :field is required" => ":field 欄は必ず入力してください。",
    /** same */
    "Field :field and :other must match" => ":field 欄と :other 欄は同じ値を入力してください。",
    /** unique */
    "Field :field must be unique" => ":field 欄の値は既に存在しています。",
    /** url */
    "Field :field must be a url" => ":field 欄は正しいURLの形式で入力してください。",
    /** with */
    "Field :field must occur together with :fields" => ":field 欄を指定する場合は :fields 欄も指定してください。",
    /** without */
    "Field :field must not occur together with :fields" => ":field 欄を指定する場合は :fields 欄を指定しないでください。",
    /* FileEmpty */
    "Field :field must not be empty" => ":field 欄が指定されていません。",
    /* FileIniSize */
    "File :field exceeds the maximum file size" => ":field 欄は最大のファイルサイズの超えています。",
    /* FileMaxResolution */
    "File :field must not exceed :max resolution" => ":field 欄には解像度 :max 以下のファイルを指定してください。",
    /* FileMinResolution */
    "File :field must be at least :min resolution" => ":field 欄には解像度 :min 以上のファイルを指定してください。",
    /* FileSize */
    "File :field exceeds the size of :max" => ":field 欄には :max 以下のサイズを指定してください。",
    /* FileType */
    "File :field must be of type: :types" => ":field 欄には次のファイル形式のものを指定してください。:types",
    /* FileValid */
    "Field :field is not valid" => ":field 欄で指定したファイルは無効です。",
];
