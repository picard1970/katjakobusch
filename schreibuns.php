<?php
session_start();
$success = "";
$error = "";

// Formularverarbeitung
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = htmlspecialchars($_POST["name"]);
  $email = htmlspecialchars($_POST["email"]);
  $message = htmlspecialchars($_POST["message"]);
  $captcha = intval($_POST["captcha"]);

  if ($captcha === $_SESSION["captcha_result"]) {
    $to = "katja.kobusch@web.de"; // <<< Hier deine Adresse einsetzen
    $subject = "Neue Nachricht von der Website";
    $body = "Name: $name\nE-Mail: $email\n\nNachricht:\n$message";
    $headers = "From: $email";

    if (mail($to, $subject, $body, $headers)) {
      $success = "Danke für deine Nachricht! Ich melde mich bald zurück.";
    } else {
      $error = "Leider ist ein Fehler aufgetreten. Bitte versuch es später noch einmal.";
    }
  } else {
    $error = "Die Rechenaufgabe war leider falsch. Bitte erneut versuchen.";
  }
}

// Neues Captcha
$a = rand(1, 9);
$b = rand(1, 9);
$_SESSION["captcha_result"] = $a + $b;
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Schreib mir – Katja Kobusch</title>
  <link rel="stylesheet" href="assets/css/styles.css?v=147">
  <script data-goatcounter="https://katjakobusch.goatcounter.com/count"
        async src="//gc.zgo.at/count.js"></script>
</head>
<body class="kontakt">
  <div class="kontakt-hero">
    <a href="index.html" class="home-link">Home</a>
    <img class="kontakt-hero__img" src="assets/images/schreib-uns-header.webp"
         alt="Vier Figuren mit Sprechblasen: »Jake! Du bist doch kein Rockstar!« – »Ich freue mich auf eure Fanpost.« – »Vielleicht wollen die Leute ja auch uns schreiben.« – »Du könntest mir schreiben.«"
         width="2172" height="600">
  </div>
  <div class="kontakt-container">
    <h1>Schreib uns!</h1>
    <p class="kontakt-intro">
      Du hast Fragen, Feedback oder einfach Lust, uns zu schreiben?<br>
      Wir freuen uns auf deine Post. 💌
    </p>

    <?php if ($success): ?>
      <p class="message success"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
      <p class="message error"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="post" action="">
      <input type="text" name="name" placeholder="Dein Name" required>
      <input type="email" name="email" placeholder="Deine E-Mail" required>
      <textarea name="message" rows="6" placeholder="Deine Nachricht..." required></textarea>
      <input type="text" name="captcha" placeholder="Was ist <?php echo $a; ?> + <?php echo $b; ?>?" required>
      <button type="submit">Absenden</button>
    </form>
  </div>

  <footer>
    <p>
      <a href="index.html">Startseite</a> |
      <a href="impressum.html">Impressum</a> |
      <a href="datenschutz.html">Datenschutz</a>
    </p>
  </footer>
</body>
</html>

