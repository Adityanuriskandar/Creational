<?php                                                                                               
 
/**
 * Interface Abstract factory mendeklarasikan metode pembuatan untuk setiap yang berbeda
 * jenis produk.
 */
interface TemplateFactory
{
    public function createTitleTemplate(): TitleTemplate;

    public function createPageTemplate(): PageTemplate;

    public function getRenderer(): TemplateRenderer;
}

/**
 * Concrete Factory ini membuat template twig.
 */
class TwigTemplateFactory implements TemplateFactory
{
    public function createTitleTemplate(): TitleTemplate
    {
        return new TwigTitleTemplate();
    }

    public function createPageTemplate(): PageTemplate
    {
        return new TwigPageTemplate($this->createTitleTemplate());
    }

    public function getRenderer(): TemplateRenderer
    {
        return new TwigRenderer();
    }
}

/**
 * Dan Concrete Factory ini membuat template PHPTemplate.
 */
class PHPTemplateFactory implements TemplateFactory
{
    public function createTitleTemplate(): TitleTemplate
    {
        return new PHPTemplateTitleTemplate();
    }

    public function createPageTemplate(): PageTemplate
    {
        return new PHPTemplatePageTemplate($this->createTitleTemplate());
    }

    public function getRenderer(): TemplateRenderer
    {
        return new PHPTemplateRenderer();
    }
}

/**
 * Setiap jenis produk yang berbeda harus memiliki antarmuka yang terpisah.
 * produk harus mengikuti antarmuka yang sama.
 *
 * Misalnya, antarmuka Produk Abstrak ini menjelaskan perilaku halaman
 * title templates.
 */
interface TitleTemplate
{
    public function getTemplateString(): string;
}

/**
 * Produk konkret ini menyediakan templat judul halaman Twig.
 */
class TwigTitleTemplate implements TitleTemplate
{
    public function getTemplateString(): string
    {
        return "<h1>{{ title }}</h1>";
    }
}

/**
 * Dan Produk konkret ini menyediakan template judul halaman PHPTemplate.
 */
class PHPTemplateTitleTemplate implements TitleTemplate
{
    public function getTemplateString(): string
    {
        return "<h1><?= \$title; ?></h1>";
    }
}

/**
 * Ini adalah jenis Produk Abstrak lainnya, yang menjelaskan seluruh templat halaman.
 */
interface PageTemplate
{
    public function getTemplateString(): string;
}

/**
  * Template halaman menggunakan sub-template judul, jadi kami harus menyediakan caranya
  * untuk mengaturnya di objek sub-templat. abstract factory akan menautkan halaman
  * template dengan template judul varian yang sama.
  */
abstract class BasePageTemplate implements PageTemplate
{
    protected $titleTemplate;

    public function __construct(TitleTemplate $titleTemplate)
    {
        $this->titleTemplate = $titleTemplate;
    }
}

/**
 * Varian Twig dari seluruh templat halaman.
 */
class TwigPageTemplate extends BasePageTemplate
{
    public function getTemplateString(): string
    {
        $renderedTitle = $this->titleTemplate->getTemplateString();

        return <<<HTML
        <div class="page">
            $renderedTitle
            <article class="content">{{ content }}</article>
        </div>
        HTML;
    }
}

/**
 * Varian PHPTemplate dari seluruh halaman template.
 */
class PHPTemplatePageTemplate extends BasePageTemplate
{
    public function getTemplateString(): string
    {
        $renderedTitle = $this->titleTemplate->getTemplateString();

        return <<<HTML
        <div class="page">
            $renderedTitle
            <article class="content"><?= \$content; ?></article>
        </div>
        HTML;
    }
}


interface TemplateRenderer
{
    public function render(string $templateString, array $arguments = []): string;
}

/**
 * The renderer for Twig templates.
 */
class TwigRenderer implements TemplateRenderer
{
    public function render(string $templateString, array $arguments = []): string
    {
        return \Twig::render($templateString, $arguments);
    }
}


class PHPTemplateRenderer implements TemplateRenderer
{
    public function render(string $templateString, array $arguments = []): string
    {
        extract($arguments);

        ob_start();
        eval(' ?>' . $templateString . '<?php ');
        $result = ob_get_contents();
        ob_end_clean();

        return $result;
    }
}


class Page
{

    public $title;

    public $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }


     // kelas halaman tidak bergantung pada kelas templat konkret apa pun.
    public function render(TemplateFactory $factory): string
    {
        $pageTemplate = $factory->createPageTemplate();

        $renderer = $factory->getRenderer();

        return $renderer->render($pageTemplate->getTemplateString(), [
            'title' => $this->title,
            'content' => $this->content
        ]);
    }
}


$page = new Page('Soto Betawi', 'Isi Body');


echo $page->render(new PHPTemplateFactory());


