<?php

namespace App\Controller\Admin;

use App\Entity\Movie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class MovieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Movie::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
        IdField::new('id')->hideOnForm(),
        TextField::new('name'),
        MoneyField::new('price')->setCurrency('EUR'),
        IntegerField::new('quantity'),
        TextareaField::new('description'),
        TextField::new('imageUrl'),
        TextField::new('status'),
        IntegerField::new('duration'),
        DateField::new('releaseDate'),
        BooleanField::new('isOver18'),
    ];
    }
    
}
