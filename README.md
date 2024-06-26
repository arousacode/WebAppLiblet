# WebAppLiblet
Small library to automatically :
  - load data from HTMl form in any PHP object, and to generate HTML inputs from it.
  - load and store data from/to the object using PDO database.

In orther to be able to manage HTMl form data in a PHP object, and to store it in a PDFO database:

## PHP example
```php
require_once('../../vendor/autoload.php');

use ArousaCode\WebApp\Types\Date;
use ArousaCode\WebApp\Types\Time;
use ArousaCode\WebApp\Types\DateTime;
use ArousaCode\WebApp\Types\TextArea;
use ArousaCode\WebApp\Types\Hidden;

class Operation
{
    use \ArousaCode\WebApp\Html\Form;

    #[Hidden]
    public ?string $operation='SAVE';
    #[Hidden]
    public ?string $mode=null;
}

class UserData
{

    use \ArousaCode\WebApp\Html\Form;
    use \ArousaCode\WebApp\Pdo\PDOExtended;

    public ?int $id;
    public string $sureName;
    public int $age;
    public float $height;
    #[DateTime]
    public \DateTime $dateOfBirth;
    #[Time]
    public \DateTime $exitTime;
    #[Date]
    public \DateTime $birthDay;
    #[TextArea]
    public string $description;
    public bool $chief;
    public ?bool $question;
}

$operation = new Operation();
$operation->loadData(INPUT_POST);

$userData = new UserData();
$userData->initDb(new \PDO("pgsql:dbname=webapp;host=webapp-postgresql","webapp","webapp"));

switch ($operation->operation) {
    case 'SAVE':
        $userData->loadData(INPUT_POST);
        $userData->upsert();
        break;
    case 'DELETE':
        $userData->loadData(INPUT_POST);
        $userData->delete();
        break;
    case 'COPY':
        $userData->id = null;
        break;
    default:
        //No operation requested: We expecto to get an ID passed by GET
        $userData->id = filter_input(INPUT_GET, 'id');
        break;
}

if($userData->id != null){
    //If there is an ID, we load the data from database. ALso if we have just stored it.
    $userData->load();
}

/*
if (isset($_POST['save'])) {
    echo "<pre> DEBUG: RECEIVED userData :\n-------------\n";
    print_r($userData);
    echo "</pre>";
}*/
?>

<html>
<form method='POST'>
    <?php $userData->printHtmlInputField('operation'); ?> 
    <?php $userData->printHtmlInputField('mode'); ?> 
    <input type='button' value='GARDAR' onclick="operation.value='SAVE';submit()"/>
    <input type='button' value='BORRAR' onclick="operation.value='DELETE';submit()"/>
    <input type='button' value='COPIAR' onclick="operation.value='COPY';submit()"/>
    <br />

    ID <?php $userData->printHtmlInputField(name: 'id', elementExtraAttributes:' readonly ' ); ?> <br />
    Name <?php $userData->printHtmlInputField('sureName'); ?> <br />
    Age <?php $userData->printHtmlInputField('age'); ?> <br />
    height <?php $userData->printHtmlInputField('height'); ?> <br />
    datebir<?php $userData->printHtmlInputField('dateOfBirth'); ?> <br />
    exit time<?php $userData->printHtmlInputField('exitTime'); ?> <br />
    birthday<?php $userData->printHtmlInputField('birthDay'); ?> <br />
    desc <?php $userData->printHtmlInputField('description'); ?> <br />
    chief <?php $userData->printHtmlInputField('chief'); ?> <br />
    question<?php $userData->printHtmlInputField('question'); ?> <br />


    <input type='button' value='GARDAR' onclick="operation.value='SAVE';submit()"/>
    <input type='button' value='BORRAR' onclick="operation.value='DELETE';submit()"/>
    <input type='button' value='COPIAR' onclick="operation.value='COPY';submit()"/>

</form>

</html>
```

The rendered HTMl Form will have this aspect:
## HTML Form rendered
<p align="center">
    <img src="https://arousacode.github.io/WebAppLiblet/images/form_example.png">
</p>


[See the docs](https://arousacode.github.io/WebAppLiblet/)