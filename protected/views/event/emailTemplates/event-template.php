<?php

use app\components\ToolKit;

if(!ToolKit::isEmpty($model->description)) {
    echo  $model->description;
}
else { ?>
    <table style="width:100%; max-width: 510px; margin: auto; overflow-x: scroll;">
        <tr>
            <td>
                <div style="margin: auto; overflow-x: hidden; height: auto;">

                </div>
            </td>
        </tr>
    </table>
<?php }
?>
