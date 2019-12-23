<?php

namespace MaiVu\Hummingbird\Lib\Helper;

use MaiVu\Hummingbird\Lib\Factory;

class Editor
{
	public static function initTinyMCE()
	{
		static $initTinyMCE = false;

		if (!$initTinyMCE)
		{
			$initTinyMCE = true;
			$vars        = array_merge(Uri::extract(), ['uri' => '/media/index', 'format' => 'raw']);
			$frameSrc    = Uri::getInstance($vars, false)->toString();
			$icon        = IconSvg::render('picture-1');
			$modalHtml   = <<<HTML
<div class="uk-modal uk-modal-container tinyMCE-image-modal"><div class="uk-modal-dialog"><button class="uk-modal-close-default" type="button" uk-close></button><div class="uk-padding-small"><iframe class="uk-width-1-1 uk-height-large" data-src="{$frameSrc}"></iframe></div></div></div>
HTML;
			Asset::addFile('core.js');
			$rootUri = ROOT_URI;
			$assets  = Factory::getService('assets');
			$assets->addJs($rootUri . '/assets/editors/tinymce/tinymce.min.js', false)
				->addInlineJs(
					<<<JAVASCRIPT
cmsCore.initTinyMCE = function (element, editorHeight) {
	var imageModal = $(element).next('.tinyMCE-image-modal');
	tinymce.init({
		target: element,
		height: editorHeight || 550,
		menubar: false,
		convert_urls: false,
		plugins: [
			'advlist autolink lists link charmap print preview anchor textcolor',
	        'searchreplace visualblocks code fullscreen',
	        'insertdatetime media table paste code wordcount'	
		],
		toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link media | table | removeformat | preview | code | fullscreen | insertImage',
		content_css: [		
			'//cdnjs.cloudflare.com/ajax/libs/uikit/3.2.0/css/uikit.min.css',
			'{$rootUri}/assets/css/tinymce.css',
		],
		setup: function (editor) {
			editor.on('change', tinyMCE.triggerSave);
        
			if (!imageModal.length) {
				imageModal = $('{$modalHtml}');
				$(element).after(imageModal);
			}
			
			editor.ui.registry.addButton('insertImage', {
				text: '{$icon}',
				onAction: function (_) {			
					var frame = imageModal.find('iframe');
					
					if (!frame.hasClass('loaded')) {
						frame.attr('src', frame.data('src')).addClass('loaded');
						frame.on('load', function () {
				            frame.contents().on('click', 'a.upload-file.image', function (e) {
				                e.preventDefault();     
				                var img = $(this).find('img').clone();
				                img.removeAttr('class');
				                img.removeAttr('title');	                
				                editor.insertContent(img[0].outerHTML);
				                UIkit.modal(imageModal[0]).hide();
				            });
			            });
					}
					
					UIkit.modal(imageModal[0]).show();
				}
			});
		}
	});
};

document.querySelectorAll('.js-editor-tinyMCE').forEach(function (element) {
	var editorHeight = 550;
	
	if (element.hasAttribute('data-editor-height')) {
		editorHeight = element.getAttribute('data-editor-height');		
	}
	
	cmsCore.initTinyMCE(element, editorHeight);
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
			Asset::addFile('core.js');
			Factory::getService('assets')
				->addCss(ROOT_URI . '/assets/editors/codemirror/lib/codemirror.min.css', false)
				->addCss(ROOT_URI . '/assets/editors/codemirror/lib/addons.min.css', false)
				->addJs(ROOT_URI . '/assets/editors/codemirror/lib/codemirror.min.js', false)
				->addJs(ROOT_URI . '/assets/editors/codemirror/lib/addons.min.js', false)
				->addJs(ROOT_URI . '/assets/editors/codemirror/mode/css/css.min.js', false)
				->addJs(ROOT_URI . '/assets/editors/codemirror/mode/xml/xml.min.js', false)
				->addJs(ROOT_URI . '/assets/editors/codemirror/mode/javascript/javascript.min.js', false)
				->addJs(ROOT_URI . '/assets/editors/codemirror/mode/htmlmixed/htmlmixed.min.js', false)
				->addInlineJs(<<<JAVASCRIPT
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
    
    $(textAreaElement).data('editor', editor);
};

document.querySelectorAll('.js-editor-codemirror').forEach(function (element) {
	cmsCore.initCodeMirror(element);
});
JAVASCRIPT
				);

		}
	}

	public static function initEditor()
	{
		self::initTinyMCE();
		self::initCodeMirror();
	}
}
