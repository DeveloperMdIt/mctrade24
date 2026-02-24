<?php

declare(strict_types=1);

namespace Template\admorris_pro\components;

use scc\ComponentRegistratorInterface;
use scc\DefaultComponentRegistrator;

// Default JTL Components 
use scc\components\Accordion;
use scc\components\Alert;
use scc\components\Badge;
use scc\components\Breadcrumb;
use scc\components\BreadcrumbItem;
use scc\components\Button;
use scc\components\ButtonGroup;
use scc\components\ButtonToolbar;
use scc\components\Card;
use scc\components\CardBody;
use scc\components\CardFooter;
use scc\components\CardGroup;
use scc\components\CardHeader;
use scc\components\CardImg;
use scc\components\Carousel;
use scc\components\CarouselSlide;
use scc\components\Checkbox;
use scc\components\CheckboxGroup;
use scc\components\Clearfix;
use scc\components\Col;
use scc\components\Collapse;
use scc\components\Container;
use scc\components\CSRFToken;
use scc\components\DropDown;
use scc\components\DropDownDivider;
use scc\components\DropDownItem;
use scc\components\Embed;
use scc\components\Form;
use scc\components\FormGroup;
use scc\components\FormRow;
use scc\components\Honeypot;
use scc\components\Input;
use scc\components\InputFile;
use scc\components\InputGroup;
use scc\components\InputGroupAddon;
use scc\components\InputGroupAppend;
use scc\components\InputGroupPrepend;
use scc\components\InputGroupText;
use scc\components\Jumbotron;
use scc\components\Link;
use scc\components\ListGroup;
use scc\components\ListGroupItem;
use scc\components\Media;
use scc\components\MediaAside;
use scc\components\MediaBody;
use scc\components\Modal;
use scc\components\Nav;
use scc\components\Navbar;
use scc\components\NavbarBrand;
use scc\components\NavbarNav;
use scc\components\NavbarToggle;
use scc\components\NavForm;
use scc\components\NavItem;
use scc\components\NavItemDropdown;
use scc\components\NavText;
use scc\components\Pagination;
use scc\components\Progress;
use scc\components\Radio;
use scc\components\RadioGroup;
use scc\components\Row;
use scc\components\Select;
use scc\components\Tab;
use scc\components\Table;
use scc\components\Tabs;
use scc\components\Textarea;
use Template\admorris_pro\components\Image\Image;
use Template\admorris_pro\components\QuantityInput\QuantityInput;

/**
 * Class CustomComponentRegistrator
 */
class CustomComponentRegistrator extends DefaultComponentRegistrator implements ComponentRegistratorInterface
{
    /**
     *
     */
    public function registerComponents(): void
    {
        if (count($this->components) > 0) {
            foreach ($this->components as $component) {
                $component->getRenderer()->preset();
                $this->renderer->registerComponent($component);
            }
        }
    }

    public function setDefaultComponents(): void
    {
        //Instantiate Default Components
        $classNames = [
            Accordion::class,
            Card::class,
            Input::class,
            Link::class,
            Button::class,
            ListGroup::class,
            ListGroupItem::class,
            Modal::class,
            Tabs::class,
            Tab::class,
            Badge::class,
            Alert::class,
            Jumbotron::class,
            DropDown::class,
            DropDownItem::class,
            DropDownDivider::class,
            ButtonGroup::class,
            ButtonToolbar::class,
            Carousel::class,
            CarouselSlide::class,
            MediaAside::class,
            MediaBody::class,
            Media::class,
            Checkbox::class,
            CheckboxGroup::class,
            Radio::class,
            RadioGroup::class,
            FormGroup::class,
            Select::class,
            Textarea::class,
            InputFile::class,
            Form::class,
            InputGroup::class,
            InputGroupAddon::class,
            InputGroupAppend::class,
            InputGroupPrepend::class,
            InputGroupText::class,
            Container::class,
            Row::class,
            Col::class,
            FormRow::class,
            Pagination::class,
            Embed::class,
            CardGroup::class,
            CardBody::class,
            CardHeader::class,
            CardFooter::class,
            CardImg::class,
            Nav::class,
            NavForm::class,
            NavText::class,
            NavItem::class,
            NavItemDropdown::class,
            Navbar::class,
            NavbarNav::class,
            NavbarBrand::class,
            NavbarToggle::class,
            Collapse::class,
            Progress::class,
            Breadcrumb::class,
            BreadcrumbItem::class,
            CSRFToken::class,
            Table::class,
            Clearfix::class,
            QuantityInput::class,
        ];

        $components = [];
        foreach ($classNames as $className) {
            $components[] = new $className();
        }
        if (class_exists('scc\components\Honeypot')) {
            $components[] = new Honeypot();
        }


        //Instantiate Custom Components
        //register Image component twice, once for opc and other plugins (image) and once for our plugin (responsiveImage) 
        $components[] = new Image('image');
        $components[] = new Image('responsiveImage');

        $this->setComponents($components);
    }
}
