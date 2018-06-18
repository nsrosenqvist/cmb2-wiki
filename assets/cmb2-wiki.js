(function($) {
    function navigate($wiki, file) {
        let $menuItems = $wiki.find('.cmb2-wiki-menu a');
        let $files = $wiki.find('.cmb2-wiki-file');
        let selector = getSelector(file);

        $menuItems.parent().removeClass('active');
        $menuItems.filter('[href="'+selector+'"]').parent().addClass('active');

        let $file = $files.filter(selector);

        if ($file.length > 0) {
            $files.hide();
            $files.filter(selector).show();
            return true;
        }
        else {
            alert("Wiki page not found: \""+file+"\"");
            return false;
        }
    }

    function getSelector(selector) {
        selector = selector.replace('.', '\\.').replace('/', '\\/');

        if (! selector.startsWith('#')) {
            selector = '#'+selector;
        }

        return selector;
    }

    function index($wiki) {
        let index = $wiki.find('.cmb2-wiki-file').first().attr('id');
        navigate($wiki, index);
    }

    $(document).on('ready', function() {
        $('.cmb2-wiki').each(function() {
            let $wiki = $(this);

            // Prepend all content wiki links with a hash
            $wiki.find('.cmb2-wiki-content a:not([href*="://"])').each(function() {
                let href = $(this).attr('href');

                if (! href.startsWith('#')) {
                    $(this).attr('href', '#'+href);
                }

                // Also enable navigation
                $(this).on('click', function() {
                    navigate($wiki, $(this).attr('href'));
                });
            });

            // Hide loader
            $wiki.find('.cmb2-wiki-loader').hide();

            // Display first file
            if (window.location.hash && $wiki.find('.cmb2-wiki-file').length > 0) {
                let success = navigate($wiki, window.location.hash);

                if (! success) {
                    index($wiki);
                }
            }
            else if ($wiki.find('.cmb2-wiki-file').length > 0) {
                index($wiki);
            }

            // Enable navigation on menu and nav buttons
            $wiki.find('.cmb2-wiki-menu a').on('click', function(event) {
                navigate($wiki, $(this).attr('href'));
            });
            $wiki.find('.cmb2-wiki-nav a').on('click', function(event) {
                navigate($wiki, $(this).attr('href'));
            });
        });
    });
})(jQuery)
