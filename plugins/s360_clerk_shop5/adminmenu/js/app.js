$(function() {
    /* Hide/show form elements based in insert method: injection vs embedded */
    const insertMethodHandling = (item) => {
        const method = item.value;
        const inputs = $(item).closest('[class^="panel-idx-"]').find(`.form-group:not(:has(#${item.id}))`);

        if (method === 'injection') {
            inputs.fadeOut();
            return;
        }

        inputs.fadeIn();
    };

    ['#omnisearch_insert_method'].forEach(el => {
        insertMethodHandling($(el).get(0));
        $(el).on('change', e => insertMethodHandling(e.target));
    });
});