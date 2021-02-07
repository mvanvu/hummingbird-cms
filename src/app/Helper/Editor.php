<?php

namespace App\Helper;

class Editor
{
	public static function initEditor()
	{
		static::initTinyMCE();
		static::initCodeMirror();
	}

	public static function initTinyMCE()
	{
		static $initTinyMCE = false;

		if (!$initTinyMCE)
		{
			$initTinyMCE = true;
			$vars        = array_merge(Uri::extract(), ['uri' => 'media/index', 'format' => 'raw']);
			$frameSrc    = Uri::getInstance($vars, false)->toString();
			$icon        = IconSvg::render('picture-1');
			$readMore    = Text::_('read-more');
			$readMoreMsg = Text::_('read-more-exists');
			$divider     = '<hr id="read-more" class="uk-divider-small"/>';
			$modalHtml   = <<<HTML
<div class="uk-modal uk-modal-container tinyMCE-image-modal" style="z-index: 9999"><div class="uk-modal-dialog"><button class="uk-modal-close-default" type="button" uk-close></button><div class="uk-padding-small"><iframe class="uk-width-1-1 uk-height-large" data-src="{$frameSrc}"></iframe></div></div></div>
HTML;
			Assets::add(['js/core.js', 'editors/tinymce/tinymce.min.js']);
			Assets::inlineJs(
				<<<JAVASCRIPT
cmsCore.initTinyMCE = function (element, editorHeight) {
	var imageModal = _$(element).next('.tinyMCE-image-modal');
	tinymce.init({
		target: element,
		height: editorHeight || 550,
		menubar: true,
		convert_urls: false,
		plugins: [
			'advlist autolink lists link charmap print preview anchor',
	        'searchreplace visualblocks code fullscreen',
	        'insertdatetime media table paste code wordcount'	
		],
		toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link media | table | removeformat | preview | code | fullscreen | readMore | insertImage',
		content_style: 'body {padding: 8px}',
		content_css: [
			'//cdnjs.cloudflare.com/ajax/libs/uikit/3.6.15/css/uikit.min.css',
		],		
		setup: function (editor) {
			editor.on('change', tinyMCE.triggerSave);
        
			if (!imageModal.length) {
				imageModal = _$('{$modalHtml}');
				_$(element).insert(imageModal);
			}
			
			editor.ui.registry.addButton('readMore', {
				text: '{$readMore}',
				onAction: function (_) {	
					if (editor.getContent().toString().match(/<hr\s+id=("|')read-more("|')[^\>]+>/i)) {
						alert('{$readMoreMsg}');
					} else {
						editor.insertContent('{$divider}');
					}											
				},
			});		
				
			editor.ui.registry.addButton('insertImage', {
				text: '{$icon}',
				onAction: function (_) {							
					var frame = imageModal.find('iframe');
					
					if (!frame.hasClass('loaded')) {						
						frame.attr('src', frame.data('src')).addClass('loaded');
						frame.on('load', function () {
							const mQ = this.contentWindow._$;
                            const contents = mQ(this.contentDocument);
				            contents.on('click', 'a.upload-file.image', function (e) {
				                e.preventDefault();     
				                var img = mQ(this).find('img').clone();
				                img.removeAttr('class');
				                img.removeAttr('title');	                
				                editor.insertContent(img.element.outerHTML);
				                UIkit.modal(imageModal.element).hide();
				            });
			            });
					}
					
					UIkit.modal(imageModal.element).show();
				},
			});
		}
	});
};

document.querySelectorAll('.js-editor-tinyMCE').forEach(function (element) {
	var editorHeight = element.getAttribute('data-editor-height') || 550;	
	cmsCore.initTinyMCE(element, parseInt(editorHeight));
});

JAVASCRIPT
			);
		}
	}

	public static function initCodeMirror()
	{
		static $initCodeMirror = false;

		if (!$initCodeMirror)
		{
			$initCodeMirror = true;
			Assets::add(
				[
					'js/core.js',
					'editors/codemirror/lib/codemirror.min.css',
					'editors/codemirror/lib/addons.min.css',
					'editors/codemirror/themes/dracula.min.css',
					'editors/codemirror/lib/codemirror.min.js',
					'editors/codemirror/lib/addons.min.js',
					'editors/codemirror/mode/css/css.min.js',
					'editors/codemirror/mode/xml/xml.min.js',
					'editors/codemirror/mode/javascript/javascript.min.js',
					'editors/codemirror/mode/htmlmixed/htmlmixed.min.js',
				]
			);
			Assets::inlineJs(<<<JAVASCRIPT
cmsCore.initCodeMirror = function (textAreaElement) {
	var editor = CodeMirror.fromTextArea(textAreaElement, {
		mode: 'htmlmixed',
        autofocus: true,
        lineWrapping: true,
        styleActiveLine: true,
        lineNumbers: true,
        gutters: [
            'CodeMirror-linenumbers',
            'CodeMirror-foldgutter',
            'CodeMirror-markergutter',
        ],
        foldGutter: true,
        markerGutter: true,        
        autoCloseTags: true,
        matchTags: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        scrollbarStyle: 'native',
        vimMode: false,
        indentUnit: 4,
        indentWithTabs: true,   
        extraKeys: {
	        F10: function(cm) {
	            cm.setOption('fullScreen', !cm.getOption('fullScreen'));
	        },
	        Esc: function(cm) {
	            if (cm.getOption('fullScreen')) cm.setOption('fullScreen', false);
	        }
        }
    });
    
    editor.on('blur', function () {
        editor.save();
    });    
    
    _$(textAreaElement).data('editor', editor).trigger('editorLoaded');   
};

document.querySelectorAll('.js-editor-codemirror').forEach(function (element) {
	cmsCore.initCodeMirror(element);
});
JAVASCRIPT
			);

		}
	}
}
