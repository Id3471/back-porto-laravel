<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nouveau message depuis le portfolio</title>
</head>
<body style="margin:0;background:#f3f5fa;color:#172033;font-family:Arial,sans-serif;">
    <main style="max-width:640px;margin:32px auto;padding:32px;background:#fff;border:1px solid #e2e7f0;border-radius:14px;">
        <p style="margin:0 0 8px;color:#536fd2;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">Formulaire du portfolio</p>
        <h1 style="margin:0 0 24px;font-size:22px;">Nouveau message</h1>
        <p style="margin:0 0 8px;"><strong>Nom :</strong> {{ $senderName }}</p>
        <p style="margin:0 0 24px;"><strong>E-mail :</strong> <a href="mailto:{{ $senderEmail }}">{{ $senderEmail }}</a></p>
        <h2 style="margin:0 0 10px;font-size:16px;">Message</h2>
        <div style="padding:16px;background:#f6f7fb;border-radius:8px;line-height:1.65;overflow-wrap:anywhere;">{!! nl2br(e($messageBody)) !!}</div>
        <p style="margin:24px 0 0;color:#68738a;font-size:13px;">Vous pouvez répondre directement à cet e-mail pour contacter son auteur.</p>
    </main>
</body>
</html>
