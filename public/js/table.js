function jgrid_init($grid, config, params, width, url, page_list, cell_call, init_params, other_params) {
    if(!page_list)
        page_list='#pager_list_2';
    url = url.split('?');
    url[1]?url_params='?' + url[1] : url_params='';
    if(!width)
        width = 110;
    $.jgrid.defaults.styleUI = 'Bootstrap';
    var colNames = [],
        colModel = [];

    for(index in config) {
        var data = {
            width: width,
            editable: true,
            //index: 'amount',
            //align: "right",
            //sorttype: "float",
            //formatter: "number"
        };
        colNames.push(config[index]['str']);
        delete config[index]['str'];
        data = $.extend(data, config[index]);
        colModel.push(data);

    }

    var init = {
        url: url[0] + 'ajax_fetch' + url_params,
        editurl: url[0] + 'ajax_edit' + url_params,
        cellurl : url[0] + 'ajax_cell' + url_params,
        datatype: "json",
        height: 450,
        //autowidth: autowidth,
        shrinkToFit: true,
        rowNum: 12,
        rowList: [10, 20, 30],
        colNames: colNames,
        colModel: colModel,
        pager: page_list,
        viewrecords: true,
        add: true,
        edit: true,
        addtext: 'Add',
        /*edittext: 'Edit',*/
        hidegrid: false,
        cellEdit : true,
        cellsubmit : 'remote',
    }
    for(var i in init_params) {
        init[i] = init_params[i];
    }
    if(cell_call) {
        init.afterSubmitCell = cell_call;
    }
    $grid.jqGrid(init);

    // Add selection
    $grid.setSelection(4, true);

    var params = $.extend({closeAfterAdd: true}, params);
    // Setup buttons
    $grid.jqGrid('navGrid', page_list, other_params?other_params:{
        edit: true,
        add: true,
        del: true,
        search: true,
    }, {
        height: 200,
        reloadAfterSubmit: true
    }, params

    );

    // Add responsive to jqGrid
    $(window).bind('resize', function () {
        var width = $('.jqGrid_wrapper').width();
        $grid.setGridWidth(width);
        $grid.setGridWidth(width);
    });
    //$('#table_list_2').editableTableWidget();
    var width = $('.jqGrid_wrapper').width();
    //$('#table_list_2').setGridWidth(width);

    $('.ui-jqgrid-view').css('width', width);
    $('.ui-jqgrid-pager').css('width', width);
    $grid.click(function (e) {
        e.stopPropagation();
    });
    $('.ui-jqgrid-bdiv').click(function(){
        var p = $grid[0].p, savedRow = p.savedRow, j, len = savedRow.length;
        if (len > 0) {
            // there are rows in cell editing or inline editing
            if (p.cellEdit) {
                // savedRow has the form {id:iRow, ic:iCol, name:nm, v:value}
                // we can call restoreCell or saveCell
                //$grid.jqGrid("restoreCell", savedRow[0].id, savedRow[0].ic);
                $grid.jqGrid("saveCell", savedRow[0].id, savedRow[0].ic);
            } else {
                // inline editing
                for (j = len - 1; j >= 0; j--) {
                    // call restoreRow or saveRow
                    //$grid.jqGrid("restoreRow", savedRow[j].id);
                    $grid.jqGrid("saveRow", savedRow[j].id);
                }
            }
        }
    });
    $.each($grid[0].grid.headers, function () {
        var $th = $(this.el), i, l, clickHandler, clickHandlers = [],
            currentHandlers = $._data($th[0], 'events');
        clickBinding = currentHandlers.click;

        if ($.isArray(clickBinding)) {
            for (i = 0, l = clickBinding.length; i < l; i++) {
                clickHandler = clickBinding[i].handler;
                clickHandlers.push(clickHandler);
                $th.unbind('click', clickHandler);
            }
        }
        $th.click(function () {
            var p = $grid[0].p, savedRow = p.savedRow, j, len = savedRow.length;
            if (len > 0) {
                // there are rows in cell editing or inline editing
                if (p.cellEdit) {
                    // savedRow has the form {id:iRow, ic:iCol, name:nm, v:value}
                    // we can call restoreCell or saveCell
                    //$grid.jqGrid("restoreCell", savedRow[0].id, savedRow[0].ic);

                    $grid.jqGrid("saveCell", savedRow[0].id, savedRow[0].ic);
                } else {
                    // inline editing
                    for (j = len - 1; j >= 0; j--) {
                        // call restoreRow or saveRow
                        //$grid.jqGrid("restoreRow", savedRow[j].id);
                        $grid.jqGrid("saveRow", savedRow[j].id);
                    }
                }
            }
        });

        l = clickHandlers.length;
        if (l > 0) {
            for (i = 0; i < l; i++) {
                $th.bind('click', clickHandlers[i]);
            }
        }
    });
}