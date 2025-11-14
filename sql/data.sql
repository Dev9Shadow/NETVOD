INSERT INTO user (email, password_hash, nom, prenom) VALUES
('marie.dubois@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLM', 'Dubois', 'Marie'),
('thomas.martin@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLM', 'Martin', 'Thomas'),
('sophie.bernard@email.com', '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890ABCDEFGHIJKLM', 'Bernard', 'Sophie');


INSERT INTO public_cible (id, nom) VALUES 
    (1, 'Tout public'),
    (2, '6+'),
    (3, '10+'),
    (4, '12+'),
    (5, '16+'),
    (6, '18+');


INSERT INTO serie (id, titre, descriptif, img, annee, date_ajout, genre, id_public_cible) VALUES
(1, 'Le lac aux mystères', 'C\'est l\'histoire d\'un lac mystérieux et plein de surprises. La série, bluffante et haletante, nous entraine dans un labyrinthe d\'intrigues époustouflantes. A ne rater sous aucun prétexte !', 'lac.jpg', 2020, '2022-10-30', 'Fantastique', 1),
(2, 'L\'eau a coulé', 'Une série nostalgique qui nous invite à revisiter notre passé et à se remémorer tout ce qui s\'est passé depuis que tant d\'eau a coulé sous les ponts.', 'eau.jpg', 1907, '2022-10-29', 'Aventure', 4),
(3, 'Chevaux fous', 'Une série sur la vie des chevaux sauvages en liberté. Décoiffante.', 'cheval.jpg', 2017, '2022-10-31', 'Western', 1),
(4, 'A la plage', 'Le succès de l\'été 2021, à regarder sans modération et entre amis.', 'plage.jpg', 2021, '2022-11-04', 'Comedie', 1),
(5, 'Champion', 'La vie trépidante de deux champions de surf, passionnés dès leur plus jeune age. Ils consacrent leur vie à ce sport.', 'surf.jpg', 2022, '2022-11-03', 'Sport', 5),
(6, 'Une ville la nuit', 'C\'est beau une ville la nuit, avec toutes ces voitures qui passent et qui repassent. La série suit un livreur, un chauffeur de taxi, et un insomniaque. Tous parcourent la grande ville une fois la nuit venue, au volant de leur véhicule.', 'ville.jpg', 2017, '2022-10-31', 'Drame', 5);


INSERT INTO episode (id, numero, titre, resume, duree, file, id_serie) VALUES
(1, 1, 'Le lac', 'Le lac se révolte', 8, 'lake.mp4', 1),
(2, 2, 'Le lac : les mystères de l\'eau trouble', 'Un grand mystère, l\'eau du lac est trouble. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(3, 3, 'Le lac : les mystères de l\'eau sale', 'Un grand mystère, l\'eau du lac est sale. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(4, 4, 'Le lac : les mystères de l\'eau chaude', 'Un grand mystère, l\'eau du lac est chaude. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(5, 5, 'Le lac : les mystères de l\'eau froide', 'Un grand mystère, l\'eau du lac est froide. Jack trouvera-t-il la solution ?', 8, 'lake.mp4', 1),
(6, 1, 'Eau calme', 'L\'eau coule tranquillement au fil du temps.', 15, 'water.mp4', 2),
(7, 2, 'Eau calme 2', 'Le temps a passé, l\'eau coule toujours tranquillement.', 15, 'water.mp4', 2),
(8, 3, 'Eau moins calme', 'Le temps des tourments est pour bientôt, l\'eau s\'agite et le temps passe.', 15, 'water.mp4', 2),
(9, 4, 'la tempête', 'C\'est la tempête, l\'eau est en pleine agitation. Le temps passe mais rien n\'y fait. Jack trouvera-t-il la solution ?', 15, 'water.mp4', 2),
(10, 5, 'Le calme après la tempête', 'La tempête est passée, l\'eau retrouve son calme. Le temps passe et Jack part en vacances.', 15, 'water.mp4', 2),
(11, 1, 'les chevaux s\'amusent', 'Les chevaux s\'amusent bien, ils ont apportés les raquettes pour faire un tournoi de badmington.', 7, 'horses.mp4', 3),
(12, 2, 'les chevals fous', '- Oh regarde, des beaux chevals !!\r\n- non, des chevaux, des CHEVAUX !\r\n- oh, bin ça alors, ça ressemble drôlement à des chevals ?!!?', 7, 'horses.mp4', 3),
(13, 3, 'les chevaux de l\'étoile noire', 'Les chevaux de l\'Etoile Noire débrquent sur terre et mangent toute l\'herbe !', 7, 'horses.mp4', 3),
(14, 1, 'Tous à la plage', 'C\'est l\'été, tous à la plage pour profiter du soleil et de la mer.', 18, 'beach.mp4', 4),
(15, 2, 'La plage le soir', 'A la plage le soir, il n\'y a personne, c\'est tout calme', 18, 'beach.mp4', 4),
(16, 3, 'La plage le matin', 'A la plage le matin, il n\'y a personne non plus, c\'est tout calme et le jour se lève.', 18, 'beach.mp4', 4),
(17, 1, 'champion de surf', 'Jack fait du surf le matin, le midi le soir, même la nuit. C\'est un pro.', 11, 'surf.mp4', 5),
(18, 2, 'surf détective', 'Une planche de surf a été volée. Jack mène l\'enquête. Parviendra-t-il à confondre le brigand ?', 11, 'surf.mp4', 5),
(19, 3, 'surf amitié', 'En fait la planche n\'avait pas été volée, c\'est Jim, le meilleur ami de Jack, qui lui avait fait une blague. Les deux amis partagent une menthe à l\'eau pour célébrer leur amitié sans faille.', 11, 'surf.mp4', 5),
(20, 1, 'Ça roule, ça roule', 'Ça roule, ça roule toute la nuit. Jack fonce dans sa camionnette pour rejoindre le spot de surf.', 27, 'cars-by-night.mp4', 6),
(21, 2, 'Ça roule, ça roule toujours', 'Ça roule la nuit, comme chaque nuit. Jim fonce avec son taxi, pour rejoindre Jack à la plage. De l\'eau a coulé sous les ponts. Le mystère du Lac trouve sa solution alors que les chevaux sont de retour après une virée sur l\'Etoile Noire.', 27, 'cars-by-night.mp4', 6);


INSERT INTO comment (id_user, id_serie, note, contenu, created_at) VALUES
(2, 2, 5, 'Nostalgie garantie ! Cette série me rappelle mon enfance. Les décors sont magnifiques.', '2024-10-20 11:15:22'),
(3, 2, 4, 'Une belle série dramatique avec de belles émotions. Parfois un peu lente mais ça vaut le coup.', '2024-10-22 16:48:10'),
(4, 2, 5, 'Coup de cœur absolu ! L''histoire est touchante et les personnages attachants.', '2024-10-25 20:33:47'),
(3, 3, 4, 'Documentaire fascinant sur la vie des chevaux. Très instructif et bien réalisé.', '2024-09-12 13:25:18'),
(4, 3, 5, 'Magnifique ! Les images sont à couper le souffle. Un must pour les amoureux des animaux.', '2024-09-14 10:52:39'),
(2, 3, 3, 'Intéressant mais un peu répétitif. Certains épisodes auraient pu être plus courts.', '2024-09-16 19:07:21'),
(1, 4, 5, 'Comédie rafraîchissante ! Parfait pour se détendre après une longue journée. Les dialogues sont hilarants.', '2024-08-05 21:15:44'),
(3, 4, 4, 'Très drôle, personnages attachants. Quelques gags tombent un peu à plat mais dans l''ensemble c''est réussi.', '2024-08-07 14:22:09'),
(2, 4, 5, 'J''adore cette série ! L''ambiance estivale met de bonne humeur. Hâte de voir la suite !', '2024-08-10 17:38:52'),
(2, 5, 5, 'Série sportive captivante ! On ressent vraiment l''adrénaline des compétitions.', '2024-07-18 12:44:31'),
(1, 5, 4, 'Bon scénario et bons acteurs. Parfois un peu cliché mais ça reste très regardable.', '2024-07-20 09:18:56'),
(3, 5, 5, 'Inspirant et motivant ! Cette série donne envie de se dépasser. Bravo aux créateurs.', '2024-07-22 15:50:13'),
(3, 6, 4, 'Ambiance polaire réussie. Le polar est bien mené même si on devine certains éléments.', '2024-06-30 20:12:27'),
(2, 6, 5, 'Excellent thriller ! Les scènes nocturnes sont magnifiquement filmées. Suspense au rendez-vous.', '2024-07-02 11:27:45'),
(4, 6, 4, 'Bonne série avec une atmosphère unique. Quelques moments un peu longs mais j''ai bien aimé.', '2024-07-05 16:41:08');

