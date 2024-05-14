<?php
require_once('../../vendor/autoload.php');

use ArousaCode\WebApp\Types\Hidden;
use ArousaCode\WebApp\Types\Date;
use ArousaCode\WebApp\Types\Time;
use ArousaCode\WebApp\Types\DateTime;
use ArousaCode\WebApp\Types\Selection;
use ArousaCode\WebApp\Types\TextArea;

class Operation
{
    use \ArousaCode\WebApp\Html\Form;

    #[Hidden]
    public ?string $operation = null;
    #[Hidden]
    public ?string $mode = null;
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

    #[Selection(schemaName: 'Test', tableName: 'Options', valueColumn: 'value', descColumn: 'description')]
    public ?string $selection;
}
/*
class UserData2
{

    use \ArousaCode\WebApp\Html\Form;
    use \ArousaCode\WebApp\Pdo\PDOExtended;

    public ?int $id;
    #[Hidden]
    public string $sureName;
    public int $age;
    public float $height;
    #[DateTime]
    public \DateTime $dateOfBirth;
    #[Time]
    public \DateTime $exitTime;
    #[Date]
    public ?\DateTime $birthDay;
    #[TextArea]
    public string $description;
    public bool $chief;
    public ?bool $question;

    public function __construct()
    {
        $this->schemaName = "Test";
    }
}*/
$operation = new Operation();
$operation->loadData(INPUT_POST);

$userData = new UserData();
$pdo = new \PDO("pgsql:dbname=webapp;host=webapp-postgresql", "webapp", "webapp");
$userData->initDb($pdo);

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

if ($userData->id != null) {
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
<! DOCTYPE html>
    <html>
    <form method='POST'>
        <?php $operation->printHtmlInputField('operation'); ?>
        <?php $operation->printHtmlInputField('mode'); ?>
        <input type='submit' value='GARDAR' />
        <input type='submit' value='GARDAR' onclick="operation.value='SAVE';return true;" />
        <input type='submit' value='BORRAR' onclick="operation.value='DELETE';return true;" />
        <input type='submit' value='COPIAR' onclick="operation.value='COPY';return true;" />
        <br />

        ID <?php $userData->printHtmlInputField(name: 'id', elementExtraAttributes: ' readonly '); ?> <br />

        <?php $userData->printHtmlLabel('sureName', 'Name') ?> <?php $userData->printHtmlInputField('sureName'); ?> <br />
        <?php $userData->printHtmlLabel('age') ?> <?php $userData->printHtmlInputField('age'); ?> <br />
        <?php $userData->printHtmlLabel('height') ?> <?php $userData->printHtmlInputField('height'); ?> <br />
        <?php $userData->printHtmlLabel('dateOfBirth', 'Date of Birth') ?> <?php $userData->printHtmlInputField('dateOfBirth'); ?> <br />

        exit time<?php $userData->printHtmlInputField('exitTime'); ?> <br />
        birthday<?php $userData->printHtmlInputField('birthDay'); ?> <br />
        desc <?php $userData->printHtmlInputField('description'); ?> <br />
        chief <?php $userData->printHtmlInputField('chief'); ?> <br />
        question<?php $userData->printHtmlInputField('question'); ?> <br />

        question<?php $userData->printHtmlInputField(name:'selection', pdo:$pdo); ?> <br />


        <input type='button' value='GARDAR' onclick="operation.value='SAVE'; submit()" />
        <input type='button' value='BORRAR' onclick="operation.value='DELETE';submit()" />
        <input type='button' value='COPIAR' onclick="operation.value='COPY';submit()" />

    </form>

    </html>