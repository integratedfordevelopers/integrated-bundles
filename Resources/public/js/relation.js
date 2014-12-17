var Relation = function(id, url) {

    this.id = id;
    this.url = url;
    this.loadedSelected = false;

    var parent = this;

    this.handleOptions = function(data) {
        var optionsTemplateSource = $('#options-template').html(), optionsTemplate = Handlebars.compile(optionsTemplateSource);
        var paginationTemplateSource = $('#pagination-template').html(), paginationTemplate = Handlebars.compile(paginationTemplateSource);

        data.title = parent.getTitle();
        data.selected = parent.getSelected();

        var container = parent.getOptionsContainer().html(optionsTemplate(data)).find('.options-cnt').append(paginationTemplate(data));
        container.find('.pagination a').click(function(ev){
            ev.preventDefault();
            parent.loadOptions($(this).attr('href'));
        });

        container.append(
            '<a href="#" data-modal="/app_dev.php/admin/content/new?class=Integrated%5CBundle%5CContentBundle%5CDocument%5CContent%5CArticle&type=blog&_format=iframe.html" class="btn btn-primary">Click to add</a>'
        );

        if ($('#relation-add').length == 0) {
            $('body').append(
                '<div id="relation-add" class="modal fade bs-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">' +
                '<div class="modal-dialog modal-lg">' +
                '<div class="modal-content">' +
                '<iframe frameborder="none" width="100%" height="400" src="">Loading</iframe>' +
                '</div>' +
                '</div>' +
                '</div>'
            );
        }

        container.find('a[data-modal]').click(function(ev){

            $('#relation-add').modal('show');
            var iFrame =  $('#relation-add').find('iframe');
            iFrame.hide().attr('src', $(this).data('modal')).load(function(){
                console.log('Loaded');
                $(this).show();

                var height = $(window).height() - 200;
                if (($(this).contents().height() + 200) < $(window).height()) {
                    height = $(this).contents().height() + 200;
                }
                iFrame.attr('height', height);

                iFrame.contents().find('*[data-dismiss="modal"]').click(function(ev){
                    ev.preventDefault();
                    $('#relation-add').modal('hide');
                })

            })
        });



        container.find('input').click(function() {
            if ($(this).is(':checked')) {
                parent.addOption($(this).val());
            } else {
                parent.removeOption($(this).val());
            }
        });

        container.find('a[data-value]').click(function(ev) {
            ev.preventDefault();
            parent.addOption($(this).data('value'));
        });

        if (parent.loadedSelected === false) {
            parent.loadSelected();
            parent.loadedSelected = true;
        }
    }

    this.handleSelected = function(data) {
        var optionsTemplateSource = $('#selected-template').html(), optionsTemplate = Handlebars.compile(optionsTemplateSource);
        var paginationTemplateSource = $('#pagination-template').html(), paginationTemplate = Handlebars.compile(paginationTemplateSource);

        data.title = parent.getTitle();
        data.selected = parent.getSelected();

        var container = parent.getSelectedContainer().html(optionsTemplate(data)).append(paginationTemplate(data));
        container.find('.pagination a').click(function(ev){
            ev.preventDefault();
            parent.loadSelected($(this).attr('href'));
        });
        container.find('*[data-remove]').click(function(ev){
            ev.preventDefault();
            parent.removeOption($(this).data('remove'));
        })
    }

    this.loadOptions = function(url) {

        this.getOptionsContainer().find('.pagination a').unbind('click').click(function(ev){
            ev.preventDefault();
        })

        if (url == undefined) {
            url = this.getOptionsUrl();
        }
        $.ajax({
            url: url,
            success: this.handleOptions
        });
    }

    this.refreshOptions = function() {
        var selected = this.getSelected();
        $('div[data-relation="' + this.id + '"] input:checked').each(function(){
            if ($.inArray($(this).val(), selected) < 0) {
                $(this).attr('checked', false);
            }
        })
    }

    this.loadSelected = function(url) {

        this.getSelectedContainer().find('.pagination a').unbind('click').click(function(ev){
            ev.preventDefault();
        })

        if (url == undefined) {
            url = this.getSelectedUrl();
        }
        $.ajax({
            url: url,
            type: 'POST',
            data: {
                id: this.getSelected()
            },
            success: this.handleSelected
        });
    }

    this.getTitle = function() {
        return this.getInputElement().data('title');
    }

    this.getOptionsUrl = function() {
        return this.url + '?relation=' + this.id + '&limit=5&q=' + $('#relations-q').val() + '&sort=title&_format=json';
    }

    this.getSelectedUrl = function() {
        return this.url + '?limit=5&sort=title&_format=json';
    }

    this.getSelected = function() {
        var selected = this.getInputElement().val().split(',');
        selected = $.grep(selected,function(n){
            return(n);
        });

        return selected;
    }

    this.getOptionsContainer = function() {
        if ($('#relations-result div[data-relation="' + this.id + '"]').length > 0) {
            return $('#relations-result div[data-relation="' + this.id + '"]');
        } else {
            var container = $('<div class="item_row" data-relation="' + this.id +'"></div>');
            $('#relations-result').append(container);
            return container;
        }
    }

    this.getSelectedContainer = function() {
        if ($('#relations-selected li[data-relation="' + this.id + '"]').length > 0) {
            return $('#relations-selected li[data-relation="' + this.id + '"]');
        } else {
            var container = $('<li data-relation="' + this.id +'"></li>');
            $('#relations-selected').append(container);
            return container;
        }
    }

    this.addOption = function(id) {
        var selected = this.getSelected();

        if (this.getMultiple() === false) {
            selected = [];
        }

        selected.push(id);

        this.getInputElement().val(selected.join(','));
        this.refreshOptions();
        this.loadSelected();
    }

    this.removeOption = function(id) {
        var selected = this.getSelected();
        if ($.inArray(id, selected) >= 0) {
            selected.splice($.inArray(id, selected), 1);
        }

        this.getInputElement().val(selected.join(','));
        this.refreshOptions();
        this.loadSelected();
    }

    this.getInputElement = function () {
        return $('input[data-relation="' + this.id + '"]');
    }

    this.getMultiple = function() {
        return (this.getInputElement().data('multiple') == 1);
    }
}