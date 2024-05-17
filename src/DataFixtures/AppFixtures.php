<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateTimeImmutable;
use App\Entity\Marque;
use App\Entity\Product;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $products = [];
        $product_names = [
            'Iron Flip', 
            'Alpha One', 
            'Honor Magic V2 RSR',
            'Meridiist',
            'Solarin',
            'Tourbillon Carbon Gold'
        ];
        $product_descriptions = [
            'La collection IRONFLIP exalte la fraternité des hommes, tirant son esthétique des films d\'aventure des années 1980. C\'est une incarnation de leur charme multiforme et de leur profondeur de caractère. Chaque pli reflète un souvenir précieux, tandis que chaque dépli affiche une confiance sereine. Au-delà d\'un simple dispositif de communication, il agit comme un canal pour les sentiments masculins, témoignant de leur chevalerie et de leurs émotions sincères.',
            'L\'Alpha One est ainsi doté d\'un cadre en LiquidMetal, un alliage promis plus résistant que le titane (idéal en cas de choc). Le dos est habillé de cuir, avec au centre une surpiqûre colorée et bien évidemment le célèbre taureau. Pour protéger l\'appareil, la marque a concocté un étui en cuir assorti, fabriqué à la main.',
            'Le PORSCHE DESIGN HONOR Magic V2 RSR est un smartphone pliable qui marie les dernières technologies à un design épuré inspiré des voitures de sport Porsche. Sa construction fine et légère en fait le téléphone pliable le plus fin et le plus léger au monde, tandis que sa charnière suspendue en forme de goutte d\'eau garantit une transition sans couture lorsqu\'il est plié et une surface exempte de plis lorsqu\'il est déplié. La caméra intégrée capture les moments les plus précieux de la vie avec la précision et les performances caractéristiques de Porsche. Le téléphone dispose d\'une double batterie HONOR en silicium-carbone de 5 000 mAh offrant une autonomie d\'utilisation pendant toute une journée.',
            'La gamme Meridiist se distingue par une coque en acier inoxydable et un écran inrayable, grâce à un double verre saphir de 60,5 carats qui se prolonge jusque sur le haut de l\'appareil. Fidèle à sa tradition d\'horloger, le fabricant a intégré une horloge digitale sur cette partie de l\'appareil. Reliée à un bouton "Tag Heuer", elle peut être orientée dans plusieurs directions et être utilisée comme chronomètre (il y a même un chronographe précis au 1/100ème de seconde). Place aussi à un appareil photo numérique de 2 mégapixels. Le tout pour une autonomie annoncée à 7 heures de communication.',
            'Destiné aux personnes détenant de nombreuses informations confidentielles dans le cadre de leur business, Solarin Smartphone se veut ultra-sécurisé et à l’abri de tout piratage. Annoncé comme le meilleur smartphone au monde, il s’équipe de technologies inédites, directement issues de la sécurité militaire. Le mobile se veut protégé de toute cyber-attaque sans pour autant altérer sa facilité d’utilisation et ses fonctionnalités.',
            'L\'idée d\'un puissant tandem constitué d\'un smartphone innovant et d\'une montre tourbillon classique a tellement captivé l\'esprit des designers de Caviar qu\'ils ont créé un autre chef-d\'œuvre, basé sur l\'alliance du passé et du futur. Il incarne l\'esprit des temps modernes, un style unique et une confiance en soi ! L\'utilisation du tourbillon - un mécanisme qui élimine les défauts de précision causés par l\'impact de la gravité - est une nouveauté absolue dans le monde des smartphones, que Caviar a mis en œuvre pour la première fois. La connexion expressive mais élégante des technologies, des mécanismes classiques, des formes nobles, de la couleur noire et du charisme du carbone est devenue un véritable jackpot stylistique et esthétique ! C\'est une combinaison parfaite, si spectaculaire et bien équilibrée qu\'on a vraiment envie de l\'appeler un standard en or.'
        ];
        $product_prices = [
            7200,
            2420,
            2699,
            8900,
            12240,
            13340
        ];

        // création de 6 téléphones de luxe
        for ($i = 0; $i < 6; $i++) {
            $product = new Product();
            $product->setName($product_names[$i]);
            $product->setDescription($product_descriptions[$i]);
            $product->setPrice($product_prices[$i]);
            $product->setCreatedAt(new DateTimeImmutable());
            $product->setUpdatedAt(new DateTimeImmutable());

            $manager->persist($product);
        }
        $manager->flush();
    }
}
