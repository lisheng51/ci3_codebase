function fixheader(tblAsJQueryObject, height) {
    var oTbl = tblAsJQueryObject;

    // for very large tables you can remove the four lines below
    // and wrap the table with <div> in the mark-up and assign
    // height and overflow property  
    var oTblDiv = $("<div id='body_content'/>");
    oTblDiv.css('height', height);
    oTblDiv.css('overflow-x', 'auto');
    oTbl.wrap(oTblDiv);

    // save original width
    oTbl.attr("data-item-original-width", oTbl.width());
    oTbl.find('thead tr th').each(function () {
        $(this).attr("data-item-original-width", $(this).width());
    });
    oTbl.find('tbody tr:eq(0) td').each(function () {
        $(this).attr("data-item-original-width", $(this).width());
    });

    // clone the original table
    var newTbl = oTbl.clone();

    // remove table header from original table
    oTbl.find('thead tr').remove();
    // remove table body from new table
    newTbl.find('tbody tr').remove();

    oTbl.parent().parent().prepend(newTbl);
    newTbl.wrap("<div id='head_content'/>");

    // replace ORIGINAL COLUMN width                
    newTbl.width(newTbl.attr('data-item-original-width'));
    newTbl.find('thead tr th').each(function () {
        $(this).width($(this).attr("data-item-original-width"));
    });
    oTbl.width(oTbl.attr('data-item-original-width'));
    oTbl.find('tbody tr:eq(0) td').each(function () {
        $(this).width($(this).attr("data-item-original-width"));
    });
}

$(function () {
    fixheader($('.tree'), 600); // 160 is height
});
