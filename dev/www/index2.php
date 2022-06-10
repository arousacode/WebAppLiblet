<?php
require_once('../../vendor/autoload.php');

use ArousaCode\WebApp\Types\Date;
use ArousaCode\WebApp\Types\Time;
use ArousaCode\WebApp\Types\DateTime;
use ArousaCode\WebApp\Types\TextArea;

class Operation
{
    use \ArousaCode\WebApp\Html\Form;

    public ?string $operation;
    public ?string $mode = null;
}

class UserData
{

    use \ArousaCode\WebApp\Html\Form;
    use \ArousaCode\WebApp\Pdo\PDOExtended;

    public ?int $id;
    public string $userName;
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
        $userData->id = filter_input(INPUT_GET, 'id');
        break;
}

if($userData->id != null){
    $userData->load();
}




if (isset($_POST['save'])) {


    echo "<pre> RECEIVED userData :\n-------------\n";
    print_r($userData);
    echo "</pre>";
}
?>

<html>
<form method='POST'>
    <input type='hidden' name='operation' value='SAVE'/>
    <input type='hidden' name='mode' value=''/>
    <input type='button' value='GARDAR' onclick="operation.value='SAVE';submit()"/>
    <input type='button' value='BORRAR' onclick="operation.value='DELETE';submit()"/>
    <input type='button' value='COPIAR' onclick="operation.value='COPY';submit()"/>
    <br />

    ID <?php $userData->printHtmlInputField(name: 'id', elementExtraAttributes:' readonly ' ); ?> <br />
    Name <?php $userData->printHtmlInputField('userName'); ?> <br />
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