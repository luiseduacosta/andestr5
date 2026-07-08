/**
 * EasyMDE Markdown Editor initialization
 * Automatically attaches to all textareas with the .markdown-editor class
 */
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('textarea.markdown-editor').forEach(function (textarea) {
        new EasyMDE({
            element: textarea,
            spellChecker: false,
            nativeSpellcheck: true,
            forceSync: true,
            autoDownloadFontAwesome: false,
            toolbar: [
                'bold', 'italic', 'strikethrough', 'heading', '|',
                'quote', 'unordered-list', 'ordered-list', '|',
                'link', 'image', '|',
                'preview', 'side-by-side', 'fullscreen', '|',
                'guide'
            ],
            renderingConfig: {
                singleLineBreaks: true,
                codeSyntaxHighlighting: true,
            },
        });
    });
});
