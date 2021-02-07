<?php

namespace App\Form\Field;

use App\Helper\Assets;
use MaiVu\Php\Form\Field\Hidden;

class CmsCommentVote extends Hidden
{
	public function toString()
	{
		$id    = $this->getId();
		$value = (int) $this->getValue();
		Assets::inlineJs(<<<JAVASCRIPT
    _$.ready(function ($) {
        var container = $('#{$id}-container');
        container.find('a').on('click', function (e) {
            e.preventDefault();            
            var a = $(this);
            var input = $('#{$id}');
            a.toggleClass('active');
            container.find('a').removeClass('uk-text-warning');
            
            if (a.hasClass('active')) {
                var p = a.addClass('uk-text-warning').parent();              
                p.prevAll().find('a').addClass('uk-text-warning');
                input.val(a.data('star'));
            } else {
                input.val(0);
            }          
        });
    });
JAVASCRIPT
		);

		$renderClass = function ($star) use ($value) {
			$class = [];

			if ($value >= $star)
			{
				$class[] = 'uk-text-warning';
			}

			if ($value == $star)
			{
				$class[] = 'active';
			}

			return $class ? ' class="' . implode(' ', $class) . '" ' : ' ';
		};

		$class1 = $renderClass(1);
		$class2 = $renderClass(2);
		$class3 = $renderClass(3);
		$class4 = $renderClass(4);
		$class5 = $renderClass(5);
		$input  = parent::toString();

		return <<<HTML
<div class="comment-vote-element-container uk-margin-small-top" id="{$id}-container">
    <ul class="uk-iconnav">
        <li>
            <a{$class1}href="#" uk-icon="star" data-star="1"></a>
        </li>
        <li>
            <a{$class2}href="#" uk-icon="star" data-star="2"></a>
        </li>
        <li>
            <a {$class3}href="#" uk-icon="star" data-star="3"></a>
        </li>
        <li>
            <a{$class4}href="#" uk-icon="star" data-star="4"></a>
        </li>
        <li>
            <a{$class5}href="#" uk-icon="star" data-star="5"></a>
        </li>
    </ul>
    {$input}
</div>
HTML;

	}
}
