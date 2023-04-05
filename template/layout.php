<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link href="/style/style.css" rel="stylesheet">
</head>
<body>
    <header>
        <h1>
            <a href="/">Telegram auth</a>
        </h1>
        <ul class="navbar-nav ml-auto">
            <li class="nav-item dropdown">
                <?php if(isset($userData) && count($userData) > 0): ?>
                    Добро пожаловать <?php echo $userData[2]; ?>, <a href="/logout.php">выйти</a>
                <?php else: ?>
                    <a href="https://t.me/phpAuthbot" target="_blank">Войти</a>
                <?php endif;?>
            </li>
        </ul>
    </header>
    <?php if(isset($_GET['error']) && $_GET['error'] == 'expired'):?>
        <div class="alert alert-danger">
            Ссылка недействительна!
        </div>
    <?php endif;?>
    <div class="user_data">
        <?php if(isset($userData) && count($userData) > 0): ?>
            <div class="fio"><?php echo $userData[3];?> <?php echo $userData[4];?></li></div>
        <?php endif;?>
        <?php if(isset($userActivity) && count($userActivity) > 0): ?>
            <table>
                <thead>
                    <th>record id</th>
                    <th>Тип активности</th>
                    <th>Дата активности</th>
                </thead>
                <tbody>
                    <?php foreach ($userActivity as $row):
                        $activityType = $row[2] == 'login' ? 'Авторизация' : 'Регистрация';
                        $activityDate = date('d.m.Y H:i:s', strtotime($row[3]));
                    ?>
                    <tr>
                        <td><?php echo $row[0]; ?></td>
                        <td><?php echo $activityType; ?></td>
                        <td><?php echo $activityDate; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif;?>
    </div>
</body>