<?php

use yii\helpers\Html;
 use yii\web\JqueryAsset;
use yii\widgets\ListView;
use yii\widgets\Pjax;
JqueryAsset::register($this);

/* @var $this yii\web\View */
/* @var $searchModel app\models\ResourceSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

 $this->title = Yii::t('messages', 'Resources');
$this->titleDescription = Yii::t('messages', 'Shared images,videos and documents');

$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'People'), 'url' => ['#']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('messages', 'Resource')];

$approveConfirm = Yii::t('messages', 'Are you sure you want to approve the resource?');
$rejectConfirm = Yii::t('messages', 'Are you sure you want to reject the resource?');
$deleteConfirm = Yii::t('messages', 'Are you sure you want to delete the resource?');


$initForm = <<<JS
 
 
   $(document).ready(function () {
        $('.search-button').click(function(){
            $('.search-form').toggle();
            return false;
        });
     
       $('.search-form form').submit(function(){
            $('#resource-grid').yiiListView('applyFilter', {
              data: $(this).serialize()
            });  
            return false;
        });
    });

	$('.delete').on('click', function() {		
		if (window.confirm("{$deleteConfirm}")) {
			$.ajax({
				type: 'GET',
				url: $(this).attr("href"),
				success: function(data){
					$('#statusMsg').html(data);
                   $.pjax.reload({container:"#pjax-id"});  //Reload GridView  
				}
			});
		}
		return false;
	});
JS;
$this->registerJs($initForm);
?>

<style type="text/css">
    .items {
        width: 100%;
    }

    .items .unwrap {
        float: left;
    }
</style>


<div class="row no-gutters">
    <div class="content-panel col-md-12">
        <div class="content-inner">
            <div class="content-area">
                <div class="form-row mb-2">
                    <div class="form-group col-md-12">
                        <?php
                        if (Yii::$app->user->checkAccess('Resource.Create')) {
                            echo Html::a("<i class=\"fa fa-plus\"></i> ".Yii::t('messages', 'Add Resource'), ['resource/create'],['class' => 'btn-primary grid-button btn']);
                        }
                        ?>
                    </div>
                </div>

                <?php Pjax::begin(
                        [
                                'id' => 'pjax-id'
                        ]
                ); ?>

                <div class="content-panel-sub">
                    <div class="panel-head">
                        <?php echo Yii::t('messages','Search by') ?>
                    </div>
                </div>
                <div class="search-form" style="display:block">
                    <?php echo $this->render('_search', ['model'=>$searchModel, 'attributeLabels'=>$attributeLabels]); ?>
                </div>
                <div class="content-panel-sub mt-4">
                    <div class="panel-head">
                        <?php echo Yii::t('messages', 'Resources') ?>
                    </div>
                </div>
                <?php
                    echo ListView::widget([
                        'id' => 'resource-grid',
                         'options' => ['class' => 'row resources', 'style' => '100%'],
                        'dataProvider' => $dataProvider,
                        'itemView' => '_view',
                        'itemOptions' => ['tag' => null],
                        'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {count} imports') . '</div>',
                        'pager' => [
                            'firstPageLabel' => '',
                            'firstPageCssClass' => 'first',
                            'activePageCssClass' => 'selected',
                            'disabledPageCssClass' => 'hidden',
                            'lastPageLabel' => 'last ',
                            'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
                            'nextPageCssClass' => 'page-item next',
                            'maxButtonCount' =>5,
                            'pageCssClass' => 'page-item',
                            'prevPageCssClass' => 'page-item previous',    // Set CSS class for the "previous" page button
                            'options' => ['class' => 'pagination justify-content-md-end'],
                        ],
                        'layout' => '<div class="col-md-12 text-right results-count mb-3">{summary}</div>
                        {items}
                        <div class="col-md-12 row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>'

                    ]);
                ?>
                <?php Pjax::end(); ?>

            </div>
        </div>
    </div>
</div>


<!-- Resource view and update dialogbox -->
<div class="modal fade" id="viewResource" tabindex="-1" role="dialog"
     aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered model-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="resourceModalTitle"><?php echo Yii::t('messages', 'Resource') ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <iframe id="iframe-resource" class="modal-body resource-details" src="" frameborder="0" scrolling="yes"
                    width="100%"  style="max-height:450px;height:90vh"></iframe>

        </div>
    </div>
</div>

<script>
    function resourcePopUp(url, title) {
        $('#iframe-resource').attr('src', url);
        var modelTitle = "<?php echo Yii::t('messages', '" + title + "')?>";
        $('#resourceModalTitle').text(modelTitle);
        $('#viewResource').modal('show');
        return false;
    }

    function resourceApprove(url) {
        if (window.confirm("<?php echo $approveConfirm ?>")) {
            $.ajax({
                type: 'GET',
                url: url,
                success: function (data) {
                    $('#statusMsg').html(data);
                    $.pjax.reload({container:"#pjax-id"});  //Reload GridView
                    return false;
                }
            });
        }
    }

    function resourceReject(url) {
        if (window.confirm("<?php echo $rejectConfirm ?>")) {
            $.ajax({
                type: 'GET',
                url: url,
                success: function (data) {
                    $('#statusMsg').html(data);
                    $.pjax.reload({container:"#pjax-id"});  //Reload GridView
                    return false;
                }
            });
        }
    }
    $( function() {
        $( ".datepicker" ).datepicker({
            maxDate: "+1D",
            dateFormat : 'yy-mm-dd',
    });
    } );

</script>
