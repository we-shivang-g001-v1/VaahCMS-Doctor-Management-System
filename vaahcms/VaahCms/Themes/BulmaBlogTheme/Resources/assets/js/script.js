$(document).ready(function() {

    let base = (document.querySelector('base') || {}).href;
    console.log(base);

    function getBlogList(q = "",category = '') {
        $.ajax({
            type: 'POST',
            url: base+'/ajax/getBlogList',
            data: {
                "_token": $('#token').val(),
                "q":q,
                "category":category
            },
            success: function (data) {
                $("#blogList").html(data);
            }
        });
    }

    $('#search-blog').on('input', function() {
        getBlogList($(this).val(),$('#category-filter.is-active').attr('data-category'));
    });


    $(document).on('click', '#category-filter', function (e) {
        e.preventDefault();
        $('#category-filter.is-active').removeClass("is-active");
        $('#category-filter').removeClass("is-active");
        $(this).addClass("is-active");

        getBlogList($('#search-blog').val(),this.dataset.category);
    });

    function run(){
        getBlogList();
    }

    run.call();

});
