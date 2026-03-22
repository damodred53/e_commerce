<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Action\Action;

class OrderCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            AssociationField::new('utilisateur')-> formatValue(function ($value) {
                return $value?->getEmail() ?? 'N/A';
            }),
            ChoiceField::new('status')
                ->setChoices([
                    'En attente' => 'pending',
                    'En cours' => 'processing',
                    'Expédiée' => 'shipped',
                    'Livrée' => 'delivered',
                    'Annulée' => 'cancelled',
                ]),
            MoneyField::new('total')->setCurrency('EUR')->hideOnForm(),
            DateTimeField::new('createdAt')->hideOnForm(),
            TextField::new('orderItems')
                ->onlyOnDetail()
                ->formatValue(function ($value, $entity) {
                    return implode('<br>', array_map(
                        fn ($item) => sprintf(
                            '%s - %d x %s EUR = %s EUR',
                            $item->getProductName(),
                            $item->getQuantity(),
                            $item->getPrice(),
                            $item->getSubtotal()
                        ),
                        $entity->getOrderItems()->toArray()
                    ));
                })
                ->renderAsHtml()
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Commande')
            ->setEntityLabelInPlural('Commandes')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }
}