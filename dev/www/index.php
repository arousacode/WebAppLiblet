<?php

require_once('../../vendor/autoload.php');

use ArousaCode\WebApp\Types\Date;
use ArousaCode\WebApp\Types\Time;
use ArousaCode\WebApp\Types\DateTime;
use ArousaCode\WebApp\Types\TextArea;

class Data{

    use \ArousaCode\WebApp\Html\Form;

    public bool $boolData;
    public ?bool $nullableBoolData; 
    public string $textData;
    #[TextArea]
    public string $textData2;
    #[Date]
    public ?DateTime $tdData1;
    #[Time]
    public ?DateTime $tdData2;
    #[DateTime]
    public ?DateTime $tdData3;
    public int $intData;
    public float $floatData;   

}

$data=new Data();

if(isset($_POST['save'])){
$data->loadData(INPUT_POST);


echo "<pre> RECEIVED DATA :\n-------------\n";
print_r($data);

echo "null : ".($data->nullableBoolData === null)?'nula':'non nula'." \n ";
echo "false : ".($data->nullableBoolData === false)?'false ':'no false'." \n ";
echo "</pre>";
}
?>

<html>
<form method='POST'>
<input type='submit' name='save' value='GARDAR' />
<br/>

<?php $data->printHtmlInputField('boolData'); ?> <br/>
<?php $data->printHtmlInputField('nullableBoolData'); ?> <br/>
<?php $data->printHtmlInputField('textData'); ?> <br/>
<?php $data->printHtmlInputField('textData2'); ?> <br/>
<?php $data->printHtmlInputField('tdData1'); ?> <br/>
<?php $data->printHtmlInputField('tdData2'); ?> <br/>
<?php $data->printHtmlInputField('tdData3'); ?> <br/>
<?php $data->printHtmlInputField('intData'); ?> <br/>
<?php $data->printHtmlInputField('floatData'); ?> <br/>



</form>

</html>