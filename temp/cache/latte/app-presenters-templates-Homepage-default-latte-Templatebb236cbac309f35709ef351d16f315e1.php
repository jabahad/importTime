<?php
// source: /home/vencs88/public_html/import_time/app/presenters/templates/Homepage/default.latte

class Templatebb236cbac309f35709ef351d16f315e1 extends Latte\Template {
function render() {
foreach ($this->params as $__k => $__v) $$__k = $__v; unset($__k, $__v);
// prolog Latte\Macros\CoreMacros
list($_b, $_g, $_l) = $template->initialize('7909b929cf', 'html')
;
// prolog Latte\Macros\BlockMacros
//
// block content
//
if (!function_exists($_b->blocks['content'][] = '_lbb5e7af87ec_content')) { function _lbb5e7af87ec_content($_b, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
;call_user_func(reset($_b->blocks['title']), $_b, get_defined_vars())  ?>

    <table>
<?php if (count($status["recnum"]) > 0) { ?>
            <tr>
                <td>errnum</td>
                <td>recnum</td>
            </tr>
            <tr>
                <td><?php echo Latte\Runtime\Filters::escapeHtml($status["errnum"], ENT_NOQUOTES) ?></td>
                <td><?php echo Latte\Runtime\Filters::escapeHtml($status["recnum"], ENT_NOQUOTES) ?></td>
            </tr>

            <tr>
                <td>-------------</td>
                <td>-------------</td>
            </tr>

            <tr>
                <td>ID závodu</td>
                <td>ID disc.</td>
                <td>ID plavce</td>
                <td>Čas</td>
                <td>Pořadí</td>
                <td>Body</td>
            </tr>
            <tr>
                <td>-----------------------</td>
                <td>--------------</td>
                <td>--------------------------------</td>
                <td>-------------------</td>
                <td>--------------</td>
                <td>--------------</td>
            </tr>
<?php $iterations = 0; foreach ($data as $col) { ?>
                    <tr>
                            <td> <?php echo Latte\Runtime\Filters::escapeHtml($col["id_event"], ENT_NOQUOTES) ?> </td>
                            <td> <?php echo Latte\Runtime\Filters::escapeHtml($col["id_stroke"], ENT_NOQUOTES) ?> </td>
                            <td> <?php echo Latte\Runtime\Filters::escapeHtml($col["id_swimmer"], ENT_NOQUOTES) ?> </td>
                            <td> <?php echo Latte\Runtime\Filters::escapeHtml($col["time"], ENT_NOQUOTES) ?> </td>
                            <td> <?php echo Latte\Runtime\Filters::escapeHtml($col["rank"], ENT_NOQUOTES) ?> </td>
                            <td> <?php echo Latte\Runtime\Filters::escapeHtml($col["point"], ENT_NOQUOTES) ?> </td>
                    </tr>
<?php $iterations++; } } else { ?>
            <tr><td colspan="3">Zvolený soubor neobsahuje výsledky domácího plaveckého oddílu.</td></tr>
<?php } ?>
    </table>

<?php
}}

//
// block title
//
if (!function_exists($_b->blocks['title'][] = '_lb6ff429a02c_title')) { function _lb6ff429a02c_title($_b, $_args) { foreach ($_args as $__k => $__v) $$__k = $__v
?>    <h1>Výsledky</h1>
<?php
}}

//
// end of blocks
//

// template extending

$_l->extends = empty($_g->extended) && isset($_control) && $_control instanceof Nette\Application\UI\Presenter ? $_control->findLayoutTemplateFile() : NULL; $_g->extended = TRUE;

if ($_l->extends) { ob_start();}

// prolog Nette\Bridges\ApplicationLatte\UIMacros

// snippets support
if (empty($_l->extends) && !empty($_control->snippetMode)) {
	return Nette\Bridges\ApplicationLatte\UIMacros::renderSnippets($_control, $_b, get_defined_vars());
}

//
// main template
//
if ($_l->extends) { ob_end_clean(); return $template->renderChildTemplate($_l->extends, get_defined_vars()); }
call_user_func(reset($_b->blocks['content']), $_b, get_defined_vars()) ; 
}}