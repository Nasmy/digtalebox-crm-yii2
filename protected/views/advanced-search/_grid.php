<?php

use app\components\WebUser;
use app\models\AuthItem;
use yii\bootstrap\Button;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use app\models\User;
use app\models\CustomField;
use app\assets\ImageAsset;

// ............Advanced search filter modifications ..............................................
$columns1 = array(
    array(
        array(
            'class' => 'yii\grid\ActionColumn',
            'template' => '{update} {delete}',
            'buttons' => [
                'update' => function ($url, $model, $key) {
                    $return = '';
                    if ((Yii::$app->user->checkAccess("People.Update"))) {
                        $return = Html::tag('a', '<i class="icon-pencil"></i>',
                            ['href' => Yii::$app->urlManager->createUrl(['people/update',
                                "id" => $model['id'], "page" => (isset($_GET['page'])) ? $_GET['page'] : '', "per-page" => (isset($_GET['per-page'])) ? $_GET['per-page'] : '',"sort" => (isset($_GET['sort'])) ? $_GET['sort'] : '',
                                "q" => base64_encode(json_encode(array("reqFrom" => "ADVANCED_SEARCH")))]),
                                'class' => 'fa fa-edit fa-lg advance-user-update',
                                'data-toggle' => 'modal',
                                'data-target' => '#updateDetails',
                                'data-backdrop' => 'static',
                                'id' => $model['id'],
                                'title' => Yii::t('messages', 'Update'),
                                'onClick' => "
                                $('#iframe-updateDetails').attr('src',$(this).attr('href'));
                                $('#updateDetails').modal({backdrop: 'static'});
                                $('#updateDetails').on('hidden.bs.modal', function (e) {
                                  $('#iframe-updateDetails').attr('src','')
                                  });
                                return false;",
                            ]);
                    }
                    return $return;
                },
                'delete' => function ($url, $model, $key) {
                    $return = '';
                    $url = Yii::$app->urlManager->createUrl(['people/delete', "id" => $model['id'], "page" => (isset($_GET['page'])) ? $_GET['page'] : '', "per-page" => (isset($_GET['per-page'])) ? $_GET['per-page'] : '',"sort" => (isset($_GET['sort'])) ? $_GET['sort'] : '',
                        "q" => base64_encode(json_encode(array("reqFrom" => "ADVANCED_SEARCH")))]);
                    if ((Yii::$app->user->checkAccess("People.Delete"))) {
                        $return = Html::a('<i class="fa fa-trash fa-lg"></i>', false, [
                            'class' => 'ajaxDelete',
                            'delete-url' => $url,
                            '_csrf-frontend' => 'Yii::$app->request->csrfToken',
                            'pjax-container' => 'people-grid-update',
                            'title' => Yii::t('app', 'Delete'),
                        ]);
                    }
                    return $return;
                },
            ]
        )
    ),
    array(
        array(
            'attribute' => 'image',
            'format' => 'html',
            'header' => Yii::t('messages', 'Profile'),
            'value' => function ($data, $row) {
                $user = new User();
                $imgSrc = $user->getPic($data->profImage);
                $gender = $user->getGenderIcon($data->gender);
                $networks = $user->getNetworkIcons($data);
                if ($networks['count'] > 1) {
                    $str = '<span class="social"><a href="#" id="network_' . $data->id . '" 
                    onclick=callToolTip("network_' . $data->id . '")
                    data-toggle="tooltip" data-html="true" title="' . $networks['network'] . '">
                    <i class="fa fa-angle-right fa-lg"></i></a></span>';
                } else {
                    $str = '<span class="social">' . $networks['network'] . '</span>';
                }

                return '<span class="profile-pic">' . $imgSrc . '</span>
            <span class="gender" data-toggle="tooltip">' . $gender . '</span>' . $str;
            },
        ),
    ),
    array(
        array(
            'attribute' => 'firstName',
            'label' => Yii::t('messages', 'First Name'),
        ),
    ),
    array(
        array(
            'attribute' => 'lastName',
            'label' => Yii::t('messages', 'Last Name'),
        ),
    ),
    array(
        array(
            'format' => 'raw',
            'attribute' => 'email',
        ),
    ),

    array(
        array(
            'attribute' => 'mobile',
            'label' => Yii::t('messages', 'Mobile'),
        ),
    ),
    array(
        array(
            'format' => 'raw',
            'attribute' => 'keywords',
            'header' => Yii::t('messages', 'Keywords'),
            'value' => function ($data, $row) {
                $user = new User();
                return $user->getUserKeywordNames($data->keywords);
            },
            'headerOptions' => ['style' => 'word-wrap: break-word;'],
        ),
    ),
    array(
        array('class' => 'yii\grid\ActionColumn',
            'template' => '{keywordUpdate}',
            'buttons' => [
                'keywordUpdate' => function ($url, $model, $key) {
                    $return = '';
                    if (Yii::$app->user->checkAccess('People.Update')) {
                        $return = Html::tag('a', '<i class="icon-pencil"></i>',
                            ['href' => Yii::$app->urlManager->createUrl(['people/update-people-ajax',
                                "id" => $model['id'], "check" => 1,"page" => (isset($_GET['page'])) ? $_GET['page'] : '', "per-page" => (isset($_GET['per-page'])) ? $_GET['per-page'] : '',"sort" => (isset($_GET['sort'])) ? $_GET['sort'] : '',
                                "q" => base64_encode(json_encode(array("reqFrom" => "ADVANCED_SEARCH")))]),
                                'class' => 'fa fa-edit fa-lg advance-user-update',
                                'title' => 'Yii::t("messages","Keyword Edit")',
                                'data-toggle' => 'modal',
                                'data-target' => '#editKeywords',
                                'data-backdrop' => 'static',
                                'id' => $model['id'],
                                'title' => Yii::t('messages', 'Update'),
                                'onClick' => "
                                $('#iframe-editKeywords').attr('src',$(this).attr('href'));
                                $('#editKeywords').modal({backdrop: 'static'});
                                $('#editKeywords').on('hidden.bs.modal', function (e) {
                                  $('#iframe-editKeywords').attr('src','')
                                  });
                                return false;",
                            ]);
                    }
                    return $return;
                }
            ]
        ),
    ),
    array(
        array(
            'format' => 'raw',
            'attribute' => 'city',
            'value' => function ($data, $row) {
                return $data->city;
            },
            'visible' => (!$mapView)
        ),
    ),

    array(
        array(
            'format' => 'raw',
            'attribute' => 'joinedDate',
            'value' => function ($data, $row) {
                return date('Y-m-d', strtotime($data->joinedDate));
            },
            'visible' => (!$mapView)

        ),
    ),
    array(
        array(
            'format' => 'raw',
            'attribute' => 'dateOfBirth',
            'label'=> Yii::t('messages', 'Age'),
            'value' => function ($data, $row) {
                return $data->dateOfBirth == User::DEFAULT_DATE_OF_BIRTH ? "N/A" : "$data->age";
            },
            'visible' => (!$mapView)
        ),
    ),

    array(
        array(
            'format' => 'raw',
            'attribute' => 'category',
            'header' => Yii::t('messages', 'Category'),
            'value' => function ($data, $row, $tagList) {
                $user = new User();
                $userTypeLabel = $user->getUserTypeLabel($data->userType);
                return '<div id="userType_' . $data->id . '"><a class="editUserType nav-item"
                href="javascript:void(0)" style="' . $userTypeLabel['color'] . '">' . $userTypeLabel['type'] . ' </a> </div>';
            }
        ),
    ),
    array(
        array('class' => 'yii\grid\ActionColumn',
            'template' => '{editUserType}',
            'buttons' => [
                'editUserType' => function ($url, $model, $key) {
                    $return = '';
                    if (Yii::$app->user->checkAccess('People.Update') && (!Yii::$app->toolKit->isEmpty($model['userType']))) {
                        $return = Html::tag('a', '<i class="icon-pencil"></i>',
                            ['href' => Yii::$app->urlManager->createUrl(['people/update-people-ajax',
                                "id" => $model['id'], "check" => 3,"page" => (isset($_GET['page'])) ? $_GET['page'] : '', "per-page" => (isset($_GET['per-page'])) ? $_GET['per-page'] : '',"sort" => (isset($_GET['sort'])) ? $_GET['sort'] : '',
                                "q" => base64_encode(json_encode(array("reqFrom" => "ADVANCED_SEARCH")))]),
                                'class' => 'fa fa-edit fa-lg advance-user-update',
                                'title' => 'Yii::t("messages","Category Edit")',
                                'data-toggle' => 'modal',
                                'data-target' => '#editCategory',
                                'data-backdrop' => 'static',
                                'id' => $model['id'],
                                'title' => Yii::t('messages', 'Update'),
                                'onClick' => "
                                $('#iframe-editCategory').attr('src',$(this).attr('href'));
                                $('#editCategory').modal({backdrop: 'static'});
                                $('#editCategory').on('hidden.bs.modal', function (e) {
                                  $('#iframe-editCategory').attr('src','')
                                  });
                                return false;",
                            ]);
                    }
                    return $return;
                }
            ],
        ),
    ),
);

$columns2 = array();
$columns3 = array();
if (!$mapView) {
    if (isset($customFields)) {
        foreach ($customFields as $key) {
            $customFieldId = $key['customFieldId'];
            $columns3[] = array(array(
                'format' => 'raw',
                'header' => $key['fieldName'],
                'value' => function ($data, $key, $index) use ($customFieldId) {
                    $return = customField::getCustomFieldValueById($data->id, $customFieldId);
                    return $return;
                },
                'headerOptions' => ['style' => 'text-align: center;'],
            ));

        }
    }

}

$columns2 = array(array(
    array(
        'format' => 'raw',
        'attribute' => 'createdAt',
        'header' => Yii::t('messages', 'Created At'),
        'value' => function ($data, $row) {
            $user = new User();
            // return date('Y-m-d H:i', strtotime($user->convertDBTime($data->createdAt)));
             return date('Y-m-d H:i', strtotime($data->createdAt));
        },
        'visible' => (!$mapView)
    ),
),
    array(
        array(
            'format' => 'raw',
            'attribute' => 'updatedAt',
            'header' => Yii::t('messages', 'Updated At'),
            'value' => function ($data, $row) {
                $user = new User();
                // return date('Y-m-d H:i', strtotime($user->convertDBTime($data->updatedAt)));
                 return date('Y-m-d H:i', strtotime($data->updatedAt));
            },
            'visible' => (!$mapView)
        ),


    ),
);

$columns = array_merge($columns1, $columns2);
$bulkExportHeaders = array();
$finalColumns = array();
$filterAttributesSet = array();
$filterAttributesSet1 = array();
$filterAttributesSet2 = array();
$filterAttributesSet3 = array();

if (!empty($filters)) {
    $filter_count = count($filters);
} else {
    $filter_count = 0;
}
if ($filter_count > 3) {
    foreach ($filters as $key => $value) {
        if ($value < 13) {
            if ($value == 6) {
                $filterAttributesSet1 = array_merge($filterAttributesSet1, $columns[$value]);
                $filterAttributesSet1 = array_merge($filterAttributesSet1, $columns[$value + 1]);
            } else if ($value == 11) {
                $filterAttributesSet1 = array_merge($filterAttributesSet1, $columns[$value]);
                $filterAttributesSet1 = array_merge($filterAttributesSet1, $columns[$value + 1]);
            } else if ($value != 7 && $value != 12) {
                $filterAttributesSet1 = array_merge($filterAttributesSet1, $columns[$value]);
            }

        }
        if ($value == 13) {
            $filterAttributesSet2 = array_merge($filterAttributesSet2, $columns2[0]);
        } else if ($value == 14) {
            $filterAttributesSet2 = array_merge($filterAttributesSet2, $columns2[1]);
        } else if ($value >= 15) {
            $filterAttributesSet3 = array_merge($filterAttributesSet3, $columns3[$value - 15]);
        }


    }


    $finalColumns = array_merge($filterAttributesSet1, $filterAttributesSet2, $filterAttributesSet3);
} else {
    foreach ($columns as $key => $value) {
        $filterAttributesSet = array_merge($filterAttributesSet, $value);
    }
    $finalColumns = $filterAttributesSet;
}

?>
<?php Pjax::begin(['id' => 'people-grid-update', 'timeout' => false]); ?>
<div class="table-wrap table-custom">
    <?= GridView::widget([
        'id' => 'people-grid',
        'dataProvider' => $dataProvider,
        'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => ''],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'summary' => '<div class="text-right results-count mt-4">' . Yii::t('messages', 'Displaying {begin}-{end} of {totalCount} results.') . '</div>',
        'pager' => [
            'firstPageLabel' => '',
            'firstPageCssClass' => 'first',
            'activePageCssClass' => 'selected',
            'disabledPageCssClass' => 'hidden',
            'lastPageLabel' => 'last ',
            'nextPageLabel' => '<span aria-hidden="true">&raquo;</span>',
            'nextPageCssClass' => 'page-item next',
            'maxButtonCount' => 5,
            'pageCssClass' => 'page-item',
            'prevPageCssClass' => 'page-item previous',    // Set CSS class for the "previous" page button
            'options' => ['class' => 'pagination justify-content-md-end'],
        ],
        'layout' => '<div class="text-right results-count">{summary}</div>
                        <div class="table-wrap">{items}</div>
                        <div class="row no-gutters d-flex flex-sm-row-reverse flex-sm-column-reverse flex-md-row"> 
                        <div class="col-md-6"></div>
                        <div class="col-md-6">
                            <div class="float-right"><nav aria-label="Page navigation">{pager}</nav></div>
                        </div></div>',
        'headerRowOptions' => array('class' => 'table-wrap table-custom'),
        'columns' => $finalColumns,

    ]); ?>
    <!-- ..............End of Advanced search filter modifications ................................ -->
</div>
<?php Pjax::end(); ?>
<!-- START Update Modal -->
<div class="modal fade" id="updateDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'Update Details'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-updateDetails" class="modal-body" src="" frameborder="0" scrolling="auto"
                    width="100%"
                    height="700px"></iframe>
        </div>
    </div>
</div>
<!-- END Update Modal -->
<!-- START View Modal -->
<div class="modal fade" id="viewDetails" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">

        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id=""> <?php echo Yii::t('messages', 'View Details'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <iframe id="iframe-viewDetails" src="" frameborder="0" scrolling="auto" width="100%"
                    height="700px"></iframe>
        </div>
    </div>
</div>
<!-- END View Modal -->

<!-- START // Edit Keywords Modal -->
<div class="modal fade" id="editKeywords" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Edit Keywords'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-editKeywords" src="" frameborder="0" scrolling="yes" width="100%"
                    height="280px"></iframe>
        </div>
    </div>
</div>
<!-- END // Edit Keywords Modal -->

<!-- START // Edit Keywords Modal -->
<div class="modal fade" id="editCategory" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"
                    id="exampleModalLongTitle"> <?php echo Yii::t('messages', 'Edit Category'); ?> </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <iframe id="iframe-editCategory" src="" frameborder="0" scrolling="yes" width="100%"
                    height="280px"></iframe>
        </div>
    </div>
</div>
<!-- END // Edit Keywords Modal -->


<!-- END Update Modal -->

<script>
    $(document).ready(function () {
        $('body').tooltip({selector: '[data-toggle="tooltip"]'});
        $('#updateDetails').on('hidden.bs.modal', function (e) {
            $("#iframe-updateDetails").attr("src", "")
        });

        $('#editKeywords').on('hidden.bs.modal', function (e) {
            $("#iframe-editKeywords").attr("src", "")
        });

        $('#editCategory').on('hidden.bs.modal', function (e) {
            $("#iframe-editCategory").attr("src", "")
        });

        $('#viewDetails').on('hidden.bs.modal', function (e) {
            $("#iframe-viewDetails").attr("src", "")
        });

        $('#sendEmail').on('hidden.bs.modal', function (e) {
            $("#iframe-sendEmail").attr("src", "")
        });
    });

    function callToolTip(id) {
        $('#' + id).tooltip('show')
    }

    function refreshSearchGrid(url) {
        var prameters = url.split('?');
        var filters = [];
        $(".filter:checked").each(function () {
            filters.push($(this).val());
        });
        var criteriaId = $("#criteriaId option:selected").val();
        var url = '<?=Yii::$app->urlManager->createUrl(['advanced-search/grid-update'])?>?' + prameters[1];
        var oldUrl = '<?=Yii::$app->urlManager->createUrl(['advanced-search/admin'])?>?' + prameters[1];
        var dataarray = $('.search-form form').serialize();

        $.ajax({
            url: url,
            type: 'post',
            data: {filters: filters, data: dataarray, criteriaId: criteriaId},
            success: function(response) {
                $.pjax.reload({container:'#people-grid-update',async: false});
            }
        });
    }

    $("#updateDetails .close").click(function () {
        var url = window.location.href;
        refreshSearchGrid(url);
    });

    $("#editKeywords .close").click(function () {
        var url = window.location.href;
        refreshSearchGrid(url);
    });
    $("#editCategory .close").click(function () {
        var url = window.location.href;
        refreshSearchGrid(url);
    });

    $('ul.pagination li a').click(function () {
        event.preventDefault();
        var url = $(this).attr('href');
        refreshSearchGrid(url);
    });

    $('.search-grid table thead tr th a').click(function () {
        event.preventDefault();
        var url = $(this).attr('href');
        refreshSearchGrid(url);
    });

</script>