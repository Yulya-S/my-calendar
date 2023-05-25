<html>
  <head>
  <style>
    body { background-image: url("img/juni.jpg"); background-size: 120%; font-family: sans-serif; font-size: 14pt; background-attachment: fixed;}
    form {
      display: flex; flex-direction: column; width: 80%; margin-left: 7%;
      padding: 40px; padding-bottom: 0;
    }
    #sort {
      width: 70%; margin-left: 12%;
      display: flex; border: 2px solid brown; margin-top: 40px; background-color: #faecee;
    }
    #sort > div:first-of-type { display: grid; grid-template-columns: 20px 20% 20px 30% 20px 25% 20px 20%; grid-gap: 5px;}
    #sort > div:first-of-type > label { text-align: left; }
    #sort > button { width: 40%; margin-left: 29%; }
    #empty { width: 40%; margin-left: 28%; }
    #empty > button { height: 50px; margin-top: -5%; margin-bottom: -20%; }
    form > div { display: grid; grid-template-columns: 35% 60%; grid-gap: 20px; margin-bottom: 40px;}
    form > div > label { text-align: right;}
    #affair { background-color: #faecee; border: 2px solid brown; margin-top: 5%; width: 70%; margin-left: 13%; }
    #affair > p {color: red; text-align: center;}
    #affair > div > input { width: 50%; }
    #affair > div > button { margin-bottom: -100px; }

    table { border-spacing: 1px; border-collapse: collapse; }
    th, td { border: 2px solid brown; background-color: #faecee; }
    th { background-image: linear-gradient(to top, #f5aeb6, #f07f8c); }
    input { font-size: 14pt; }
    .txt { border: 0; background: none; text-decoration: underline; font-size: 12pt; margin-bottom: 0;}
    button { border-radius: 20px; height: 30px; font-size: 14pt; margin-bottom: 30px; }
    button:hover { background-color: #f5aeb6; }
    .txt:hover { background: none; color: #f07f8c; }
  </style>
  </head>
  <body>
    <?php
      session_start();
      $_SESSION['error'] = False;

      if (isset($_POST['update'])) {
        $_SESSION['regim'] = 'update';
        $_SESSION['post_id'] = $_POST['update'];
      }
      if (isset($_POST['create'])) $_SESSION['regim'] = 'create';
      if (isset($_POST['back'])) $_SESSION['regim'] = 'show';

      $dbname = 'calendar';
      $username = 'root';
      $password = '';
      $host = 'localhost';
      $dbo = new PDO(
        "mysql:host=$host;dbname=$dbname",
        $username,
        $password
      );

      if (isset($_POST['enter'])) foreach ($_POST as $ps) if ($ps == '') $_SESSION['error'] = True;
      if ($_SESSION['error'] == False and isset($_POST['enter'])){
        if ($_POST['enter'] == 'update'){
          $sql = $dbo->prepare( "UPDATE `affairs` SET `description` = :desk, `type` = :type, `place` = :place, `end_date` = :dt WHERE `id` = {$_SESSION['post_id']} LIMIT 1;");
          $sql->execute( [':desk' => $_POST['description'], ':type' => $_POST['type'], ':place' => $_POST['place'], ':dt' => $_POST['end_date']]);
          $_SESSION['regim'] = 'show';
          header('Location: index.php');
        }
        elseif ($_POST['enter'] == 'create'){
          $sql = $dbo->prepare( "INSERT INTO `affairs` (`description`, `type`, `place`, `end_date`) VALUES (:desk, :type, :place, :dt);");
          $sql->execute( [':desk' => $_POST['description'], ':type' => $_POST['type'], ':place' => $_POST['place'], ':dt' => $_POST['end_date']]);
          $_SESSION['regim'] = 'show';
          header('Location: index.php');
        }
      }
      if (isset($_POST['end'])){
        $sql = $dbo->prepare( "SELECT `done` FROM `affairs` WHERE `id` = {$_POST['end']} LIMIT 1;");
        $sql->execute();
        $done = $sql->fetch();
        if ($done['done'] == '0') $done = '1';
        else $done = '0';
        $sql = $dbo->prepare( "UPDATE `affairs` SET `done` = :done WHERE `id` = {$_POST['end']} LIMIT 1;");
        $sql->execute( [':done' => $done]);
      }

      if (isset($_POST['delete'])){
        $sql = $dbo->prepare( "DELETE FROM `affairs` WHERE `id` = {$_POST['delete']};" );
        $sql->execute();
      }

      if (!isset($_SESSION['regim']) or $_SESSION['regim'] == 'show'){
        echo "<form action='#' method='post' id='sort'><div>";
        if (isset($_POST['how_sort']) and $_POST['how_sort'] == 'now') echo '<input type="radio" id="now" name="how_sort" value="now" checked><label for="now">Текущие</label>';
        else echo '<input type="radio" id="now" name="how_sort" value="now"><label for="now">Текущие</label>';
        if (isset($_POST['how_sort']) and $_POST['how_sort'] == 'last') echo '<input type="radio" id="last" name="how_sort" value="last" checked><label for="last">Просроченные</label>';
        else echo '<input type="radio" id="last" name="how_sort" value="last"><label for="last">Просроченные</label>';
        if (isset($_POST['how_sort']) and $_POST['how_sort'] == 'done') echo '<input type="radio" id="done" name="how_sort" value="done" checked><label for="done">Выполненные</label>';
        else echo '<input type="radio" id="done" name="how_sort" value="done"><label for="done">Выполненные</label>';
        if (!isset($_POST['how_sort']) or $_POST['how_sort'] == 'all') echo '<input type="radio" id="all" name="how_sort" value="all" checked><label for="all">Все</label>';
        else echo '<input type="radio" id="all" name="how_sort" value="all"><label for="all">Все</label>';
        echo "</div>";

        echo '<div><label for="date">Определенная дата</label><input type="date" name="date"></div>';
        echo "<button type='submit' name='sort'>сортировать</button>";
        echo "<button type='submit' name='create' >Создать запись</button>";
        echo "</form>";

        echo "<form action='#' method='post'><table>";
        echo "<tr><th style='width: 97px;'>Выполнено</th><th style='width: 140px;'>Тип</th><th>Задача</th><th style='width: 120px;'>Место</th><th style='width: 120px;'>Дата завершения</th><th style='width: 86px;'></th><th style='width: 76px;'></th><th style='width: 98px;'></th></tr>";
        $sql = "SELECT * FROM `affairs` WHERE `id` != ''";

        if (isset($_POST['date']) and $_POST['date'] != '') $sql .= " AND `end_date` = :dt";
        elseif (isset($_POST['how_sort']) and $_POST['how_sort'] == 'done') $sql .= " AND `done` = 1";
        elseif (isset($_POST['how_sort']) and $_POST['how_sort'] == 'now') $sql .= " AND `done` = 0 AND `end_date` >= :dt";
        elseif (isset($_POST['how_sort']) and $_POST['how_sort'] == 'last') $sql .= " AND `done` = 0 AND `end_date` < :dt";

        $sql = $dbo->prepare( $sql." ORDER BY `end_date` DESC;" );
        if (isset($_POST['date']) and $_POST['date'] != '') $date = $_POST['date'];
        else $date = date('Y-m-d');
        if ((isset($_POST['how_sort']) and ($_POST['how_sort'] == 'now' or $_POST['how_sort'] == 'last')) or (isset($_POST['date']) and $_POST['date'] != '')) $sql->execute( [':dt' => $date] );
        else $sql->execute();
        $data = $sql->fetchAll(PDO::FETCH_ASSOC);
        if ($sql->rowCount() != 0){
          $headers = array_keys($data[0]);
          foreach ($data as $dt) {
            echo "<tr>";
            if ( $dt[$headers[4]] == 1 ) echo "<td style='color: green'>Завершено</td>";
            elseif ($dt[$headers[4]] == 0 and $dt[$headers[5]] < date('Y-m-d')) {
              echo "<td style='color: red'>Просрочено</td>";
            }
            else echo "<td>В процессе</td>";
            echo "<td style='text-align: center;'>{$dt[$headers[2]]}</td><td>{$dt[$headers[1]]}</td><td style='text-align: center;'>{$dt[$headers[3]]}</td><td>{$dt[$headers[5]]}</td>";
            echo "<td><button type='submit' name='update' class='txt' value='{$dt['id']}'>изменить</button></td><td><button type='submit' name='delete' class='txt' value='{$dt['id']}'>удалить</button></td>";
            if ($dt[$headers[4]] == 0) echo "<td><button type='submit' name='end' class='txt' value='{$dt['id']}'>Завершить</button></td></tr>";
            else echo "<td><button type='submit' style='font-size: 10pt;' name='end' class='txt' value='{$dt['id']}'>Отменить завершение</button></td></tr>";
          }
        }
        echo "</table>";
        echo "</form>";
      }
      else {
        if ($_SESSION['regim'] == 'update' AND !isset($_POST['description'])){
          $sql = $dbo->prepare( "SELECT * FROM `affairs` WHERE `id` = {$_SESSION['post_id']} LIMIT 1;");
          $sql->execute();
          if ($sql->rowCount() != 0) $date = $sql->fetch();
          $_POST = $date;
        }
        echo "<form action='#' method='post' id='affair'>";
        if (isset($_SESSION['error']) and $_SESSION['error'] == 'true') echo '<p>Ошибка заполнения полей</p>';
        if (!isset($_POST['description'])) $_POST['description'] = '';
        echo "<div><label for='description'>Задача:</label><input type='text' name='description' value='{$_POST['description']}'>";
        if (!isset($_POST['type'])) $_POST['type'] = '';
        echo "<label for='type'>Тип:</label><input type='text' name='type' value='{$_POST['type']}'>";
        if (!isset($_POST['place'])) $_POST['place'] = '';
        echo "<label for='place'>Место:</label><input type='text' name='place' value='{$_POST['place']}'>";
        if (!isset($_POST['end_date'])) $_POST['end_date'] = '';
        echo "<label for='end_date'>дата</label><input type='date' name='end_date' value='{$_POST['end_date']}'></div>";
        echo "<div><button type='submit' name='back'>Отменить</button>";
        if ($_SESSION['regim'] == 'update') echo "<button type='submit' name='enter' value='update'>Применить</button>";
        if ($_SESSION['regim'] == 'create') echo "<button type='submit' name='enter' value='create'>Создать</button>";
        echo "</div></form>";
      }
    ?>
  </body>
</html>
