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
  <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
  <div class="kontakt-container">
    <h1>Schreib mir!</h1>
    <h2>
      Du hast Fragen, Feedback oder einfach Lust, mir zu schreiben?
    </h2>
    <p>
      Auch wenn mein erstes Buch erst Herbst/Winter 2025 erscheint, freue ich mich natürlich auch jetzt schon über Post.
    </p>
    <!--
    <p>
      Vielleicht möchtest du mir erzählen, wie dir <em>Echo & Ember</em> gefallen hat – ob du mit Jake mitgelitten hast, Anouks Sprüche feierst oder ob Eike dir auf die Nerven gegangen ist.<br><br>
      Ich freue mich über Post – ganz gleich, ob:
    </p>
    <ul>
      <li>Lob</li>
      <li>Leseeindruck</li>
      <li>literarischer Seufzer</li>
      <li>Fragen</li>
      <li>oder auch Kritik (bitte konstruktiv 😉)</li>
    </ul>
    <h2>
      Und natürlich leite ich auch Fan- und Liebesbriefe an Jake & Co weiter.
    </h2>
    <p>
      (Ob sie antworten, kann ich allerdings nicht versprechen.)
    </p>
    -->
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
      <a href="buecher.html">Bücher</a> |
      <a href="ideenmeer.html">IdeenMeer</a> |
      <a href="ueberkatja.html">Über Katja</a> |
      <a href="impressum.html">Impressum</a> |
      <a href="datenschutz.html">Datenschutz</a>
    </p>
  </footer>
</body>
</html>
