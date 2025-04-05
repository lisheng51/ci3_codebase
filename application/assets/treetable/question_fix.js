function treeSortList(pathajax = "back/Bookmark/sortList") {
    /**
     * Checks that the moved row is within its category still by looking at it's adjacent rows.
     * If one of the adjacent rows matches the f_question_id data attribute, returns true.
     * Else it returns false.
     */
    function isValidLocation(row) {
        var ofSameNode = $('[data-f_question_id="' + $(row).data('f_question_id') + '"]', "tbody#itemContainer");
        var prev = row.prev();
        var next = row.next();
        var validLoc = false;
        ofSameNode.each(function (index, element) {
            if ($(element).html() == $(prev).html() || $(element).html() == $(next).html()) {
                validLoc = true;
            }
        });
        return validLoc;
    }

    /**
     * Looks for children in list and re-attaches them directly after their parent.
     */
    function rebuildTree(parent) {
        var children = getAllChildren(parent, []);
        if (children.length > 0) {
            $(children).detach().insertAfter($(parent));
        }
    }

    /**
     * Get all children where TreeGrid's getChildNodes doesn't.
     */
    function getAllChildren(element, list) {
        var tree = getEntireTree(element, list);
        tree.shift();
        return tree;
    }

    /**
     * Gets the entire tree (including parent).
     */
    function getEntireTree(element, list) {
        var children = $(element).siblings('[data-f_question_id="' + $(element).attr('id') + '"]');
        list.push(element);
        children.each(function (index, child) {
            getEntireTree(child, list);
        });
        return list;
    }

    var ajaxurl = site_url + pathajax;
    $('tbody#itemContainer').sortable({
        opacity: 0.6,
        revert: true,
        scroll: true, scrollSensitivity: 100, scrollSpeed: 100,
        items: "tr",
        handle: 'button#start_ajax_sort',
        cancel: '',
        cursor: 'move',
        helper: function (e, ui) {
            ui.children().each(function () {
                $(this).width($(this).width());
                $(this).css('maxWidth', $(this).outerWidth());
            });
            return ui;
        },
        /**
         * SORTSTART: This event is triggered when sorting starts.
         * https://api.jqueryui.com/sortable/#event-start
         * 
         * Prevents preview row from escaping eligable areas.
         * Shows eligable/ineligable areas for drop.
         */
        start: function (event, ui) {
            // Fixes table shrinking because it's replicating the hidden input field in TR as a visible field.
            $(ui.placeholder).children().first().css('display', 'none');

            // Force placeholder height to be equal to actual row's height. 
            $(ui.placeholder).height($(ui.helper).height());

            // Get current row's parent ID
            var parent_id = ui.item[0].dataset.f_question_id;

            // If parent_id > 0, then currentRow is a child row
            if (parent_id > 0) {
                // Instantiate new sortable on specific rows 
                var items = 'tr.treegrid-parent-' + parent_id + '';
                $('tbody#itemContainer').sortable({ items: items });
            } else {
                // currentRow is a main category - sort between other parents
                $('tbody#itemContainer').sortable({ items: 'tr.parent' });
            }

            // Triggers the reloading of all sortable items, causing new items to be recognized.
            $('tbody#itemContainer').sortable("refresh");
            var currentRow = $(ui.item).closest('tr').first();
            currentRow.data('isCollapsedState', currentRow.treegrid('isCollapsed'));
            currentRow.treegrid('collapse');
            var ofSameNode = $('[data-f_question_id="' + parent_id + '"]', "tbody#itemContainer");
            var notOfSameNode = $('tr', "tbody#itemContainer").not('[data-f_question_id="' + parent_id + '"]');
            $(notOfSameNode).addClass('dropNotPermitted');
            $(ofSameNode).addClass('dropPermitted');
        },

        /**
         * BEFORESTOP: This event is triggered when sorting stops, but when the placeholder/helper is still available.  
         * https://api.jqueryui.com/sortable/#event-beforeStop
         * 
         * Removes styling added to ui.helper during sorting before inserting it back into table.
         */
        beforeStop: function (e, ui) {
            $(ui.helper).removeAttr("style");
            ui.helper.children().each(function () {
                $(this).removeAttr("style");
            });
        },

        /**
         * SORTSTOP: Event launched after dragging regardless of having changed DOM or not.
         * https://api.jqueryui.com/sortable/#event-stop 
         * 
         * Restore collapsed state if needed.
         * Remove class with background property.
         */
        stop: function (e, ui) {
            var currentRow = $(ui.item).closest('tr').first();
            if (!currentRow.data('isCollapsedState')) {
                currentRow.treegrid('expand');
                currentRow.removeData('isCollapsedState');
            }
            $('tr').removeClass('dropPermitted dropNotPermitted');
        },

        /** 
         * SORTUPDATE: This event is triggered when the user stopped sorting and the DOM position has changed.
         * https://api.jqueryui.com/sortable/#event-update
         * 
         * If row is not adjacent at either side with a row belonging to the same parent, undo changes.
         */
        update: function (e, ui) {
            var currentRow = $(ui.item).closest('tr').first();

            // If neither of the neighbouring elements belong to original parent, then it's not a valid location.
            if (!isValidLocation(currentRow)) {
                $(this).sortable('cancel')
            } else {
                rebuildTree(currentRow)

                // Just in case row has been inserted between parent and it's children, rebuild previous row as well.
                // This is because treegrid works with visibility when collapsing items instead of removing them from the DOM.
                // So in actuality, when a row is moved underneath a collapsed parent row,
                // it inserts it between the parent and its children.
                rebuildTree(currentRow.prev());
            }

            // Push results to DB.
            let ul = $(ui.item).closest('tbody#itemContainer');
            let index = 0;
            let form_data = new FormData();
            form_data.set([csrf_token_name], csrf_hash);
            ul.find('>tr').each(function () {
                index++;
                $(this).find('input').val(index);
                let formkey = $(this).find('input').attr('name');
                form_data.set(formkey, index);
            });
            axios({
                method: 'post',
                url: ajaxurl,
                data: form_data
            }).then((response) => {

            }).catch((error) => {
                const jqxhr = error.response;
                if (typeof jqxhr !== 'undefined') {
                    message_ajax_fail_show(jqxhr);
                }
                console.log(error);
            }).then((response) => {
                //currentEle.data('value', newValue);
            });
        }
    });

}