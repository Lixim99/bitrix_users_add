<?require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
GLOBAL $USER;
if (!$USER->IsAdmin()) {
    die();
}

use \Bitrix\Main\Loader;
use Bitrix\Main\PhoneNumber\Format;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\UserTable;

if ($_GET['delete'] == 'Y') {
    $rsUsers = UserTable::query()
        ->where('TIMESTAMP_X', '>=', (new \Bitrix\Main\Type\DateTime())->setDate(2021, 3, 29))
        ->setSelect(['ID'])
        ->exec();

    while ($userRow = $rsUsers->fetch()) {
        \CUser::Delete($userRow['ID']);
    }
    die;
}

Loader::includeModule('highloadblock');

const HIGHLOAD_USERS = [3, 4];

$sites = [
    4 => 'newsitelinz',
    3 => 'sitelinz'
];

$arHighBlock = [];

foreach (HIGHLOAD_USERS as $highloadId) {
    $arHighBlock[$highloadId] = [
        'USERS' => userGetter($highloadId),
        'SITE' => $sites[$highloadId]
    ];
}

die;
$arPhones = [];
$userObj = new \CUser;

foreach ($arHighBlock as $arUsers) {
    $site = $arUsers['SITE'];

    foreach ($arUsers['USERS'] as $arUser) {
        $userId = 0;
        $formattedPhone = $arUser['UF_PHONE'];
        $alreadyExistUser = $arPhones[$formattedPhone];

        if (empty($alreadyExistUser)) {
            /*$userId = $userObj->Add([
                'NAME' => $arUser['UF_NAME'],
                'LAST_NAME' => $arUser['UF_LAST_NAME'],
                'SECOND_NAME' => $arUser['UF_SECOND_NAME'],
                'LOGIN' => $arUser['UF_LOGIN'],
                'PASSWORD' => $arUser['UF_PASS'],
                'CHECKWORD' => $arUser['UF_CHECK'],
                'EMAIL' => $arUser['UF_EMAIL'],
                'ACTIVE' => $arUser['UF_ACTIVE'],
                'PERSONAL_PHONE' => $arUser['UF_PHONE'],
            ]);
            if (empty($userId)) {
                echo '<pre>';
                print_r($userObj->LAST_ERROR);
                echo '</pre>';
                die;
            }*/

            if (!empty($formattedPhone)) {
                $arPhones[$formattedPhone] = [
                    'ID' => $userId,
                    'OLD_ID' => $arUser['UF_USER_ID'],
                    'SITE' => $site,
                ];
            }
        } else {

        }
    }
    die;
}

function userGetter($highloadId)
{
    $entityDataClass = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($highloadId)->getDataClass();

    $result = $entityDataClass::getList([
        "select" => array("*"),
    ]);

    while ($user = $result->fetch()) {
        $phone = str_replace([' ', '&nbsp;'], '', $user['UF_PHONE']);
        $parsePhone = Parser::getInstance()->parse($phone)->format(Format::E164);
        $user['UF_PHONE'] = str_replace('+', '', $parsePhone);

        $arUsers[$user['UF_PHONE']] = $user;

    }
    die;

    return $arUsers ?? [];
}

