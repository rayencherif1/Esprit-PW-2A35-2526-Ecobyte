SET NAMES utf8mb4;

-- -----------------------------
-- Exercices (insert idempotent)
-- -----------------------------
INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Développé couché haltères', 'musculation',
       'Allongé sur banc, descendre les haltères de façon contrôlée puis pousser sans cambrer excessivement.',
       'Renforce pectoraux, triceps et stabilité des épaules.',
       'https://images.unsplash.com/photo-1599058917212-d750089bc07e?w=800', '', 10, 4
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Développé couché haltères');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Rowing barre buste penché', 'musculation',
       'Dos gainé, tirer la barre vers le nombril, redescendre lentement sans arrondir le dos.',
       'Développe le dos, améliore posture et force de tirage.',
       'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?w=800', '', 8, 12
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Rowing barre buste penché');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Presse à cuisses', 'musculation',
       'Pieds à largeur d’épaules, descendre contrôlé puis pousser fort sans verrouiller complètement les genoux.',
       'Renforce quadriceps et fessiers, utile pour la progression jambe.',
       'https://images.unsplash.com/photo-1574680178050-55c6a6a96e0a?w=800', '', 12, 10
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Presse à cuisses');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Soulevé de terre roumain', 'musculation',
       'Hinge de hanche, barre proche du corps, remonter en contractant ischios et fessiers.',
       'Renforce chaîne postérieure, améliore puissance et stabilité.',
       'https://images.unsplash.com/photo-1552674605-db6ffd4facb5?w=800', '', 8, 11
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Soulevé de terre roumain');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Planche dynamique', 'cardio',
       'Alterner appuis coudes/mains en gardant le tronc gainé, respiration régulière.',
       'Travail cardio + gainage, augmente la dépense énergétique.',
       'https://images.unsplash.com/photo-1518611012118-696072aa579a?w=800', '', 45, 6
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Planche dynamique');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Rameur intervalle', 'cardio',
       'Intervalles 30s rapide / 30s modéré, posture droite et traction complète.',
       'Améliore VO2, endurance et condition générale.',
       'https://images.unsplash.com/photo-1583454110551-21f2fa2afe61?w=800', '', 12, 12
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Rameur intervalle');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Montées de genoux', 'cardio',
       'Course sur place intense avec genoux hauts et bras actifs.',
       'Augmente fréquence cardiaque rapidement, utile en HIIT.',
       'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800', '', 40, 6
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Montées de genoux');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Jumping jacks', 'cardio',
       'Sauts latéraux coordonnés bras/jambes, cadence stable.',
       'Échauffe tout le corps, améliore endurance cardio.',
       'https://images.unsplash.com/photo-1571019613914-85f342c1d4b2?w=800', '', 50, 2
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Jumping jacks');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Fentes marchées', 'perte_de_poids',
       'Grand pas, genou arrière vers le sol, alterner jambe gauche/droite.',
       'Tonifie jambes/fessiers et augmente la dépense calorique.',
       'https://images.unsplash.com/photo-1517963879433-6ad2b056d712?w=800', '', 16, 10
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Fentes marchées');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Mountain climbers', 'perte_de_poids',
       'Position planche, ramener les genoux rapidement vers la poitrine en alternance.',
       'Excellent pour brûler des calories et renforcer le tronc.',
       'https://images.unsplash.com/photo-1594737625785-a6cbdabd333c?w=800', '', 40, 6
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Mountain climbers');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Corde à sauter', 'perte_de_poids',
       'Sauts légers sur l’avant-pied, épaules relâchées, rythme progressif.',
       'Très bon ratio temps/calories pour la perte de poids.',
       'https://images.unsplash.com/photo-1599058918144-1ffabb6ab9a0?w=800', '', 120, 7
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Corde à sauter');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Thrusters haltères', 'perte_de_poids',
       'Squat puis poussée épaules au-dessus de la tête en un seul mouvement.',
       'Mouvement complet très efficace pour le métabolisme.',
       'https://images.unsplash.com/photo-1549060279-7e168fcee0c2?w=800', '', 12, 2
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Thrusters haltères');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Sprint vélo stationnaire', 'perte_de_poids',
       '20 secondes sprint / 40 secondes récupération active, répéter plusieurs tours.',
       'Très bon effort fractionné pour la dépense calorique.',
       'https://images.unsplash.com/photo-1517836357463-d25dfeac3438?w=800', '', 12, 10
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Sprint vélo stationnaire');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Skaters latéraux', 'perte_de_poids',
       'Sauts latéraux alternés avec réception contrôlée sur une jambe.',
       'Travail cardio + coordination, augmente la dépense énergétique.',
       'https://images.unsplash.com/photo-1571019614242-c5c5dee9f50b?w=800', '', 30, 8
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Skaters latéraux');

INSERT INTO exercices (nom, type_exercice, etapes, benefices, url_image, url_video, nb_repetitions_suggerees, muscle_wger_id)
SELECT 'Kettlebell swing', 'perte_de_poids',
       'Hinge de hanche explosif, balancer la kettlebell jusqu''à hauteur poitrine.',
       'Excellent mouvement métabolique pour brûler des calories.',
       'https://images.unsplash.com/photo-1599058945525-550d2b8d4b5e?w=800', '', 20, 11
WHERE NOT EXISTS (SELECT 1 FROM exercices WHERE nom = 'Kettlebell swing');

-- -----------------------------
-- Programmes (insert idempotent)
-- -----------------------------
INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Force fondamentale 3 jours', 6, 'musculation'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Force fondamentale 3 jours');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Hypertrophie haut du corps', 8, 'musculation'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Hypertrophie haut du corps');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Jambes et chaîne postérieure', 6, 'musculation'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Jambes et chaîne postérieure');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Cardio intervalle débutant', 4, 'cardio'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Cardio intervalle débutant');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Endurance active maison', 5, 'cardio'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Endurance active maison');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'HIIT métabolique 20 min', 4, 'perte_de_poids'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'HIIT métabolique 20 min');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Perte de poids progressive', 8, 'perte_de_poids'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Perte de poids progressive');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Reprise en douceur maison', 3, 'perte_de_poids'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Reprise en douceur maison');

INSERT INTO programmes (nom, duree_semaines, type_programme)
SELECT 'Perte de poids intensité progressive', 6, 'perte_de_poids'
WHERE NOT EXISTS (SELECT 1 FROM programmes WHERE nom = 'Perte de poids intensité progressive');

-- --------------------------------------------
-- Liaison programmes <-> exercices (idempotent)
-- --------------------------------------------
INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 8
FROM programmes p
JOIN exercices e ON e.nom = 'Développé couché haltères'
WHERE p.nom = 'Force fondamentale 3 jours'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 8
FROM programmes p
JOIN exercices e ON e.nom = 'Rowing barre buste penché'
WHERE p.nom = 'Force fondamentale 3 jours'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 10
FROM programmes p
JOIN exercices e ON e.nom = 'Presse à cuisses'
WHERE p.nom = 'Force fondamentale 3 jours'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 10
FROM programmes p
JOIN exercices e ON e.nom = 'Développé couché haltères'
WHERE p.nom = 'Hypertrophie haut du corps'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 10
FROM programmes p
JOIN exercices e ON e.nom = 'Rowing barre buste penché'
WHERE p.nom = 'Hypertrophie haut du corps'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 12
FROM programmes p
JOIN exercices e ON e.nom = 'Planche dynamique'
WHERE p.nom = 'Hypertrophie haut du corps'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 10
FROM programmes p
JOIN exercices e ON e.nom = 'Presse à cuisses'
WHERE p.nom = 'Jambes et chaîne postérieure'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 8
FROM programmes p
JOIN exercices e ON e.nom = 'Soulevé de terre roumain'
WHERE p.nom = 'Jambes et chaîne postérieure'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 16
FROM programmes p
JOIN exercices e ON e.nom = 'Fentes marchées'
WHERE p.nom = 'Jambes et chaîne postérieure'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 12
FROM programmes p
JOIN exercices e ON e.nom = 'Rameur intervalle'
WHERE p.nom = 'Cardio intervalle débutant'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 40
FROM programmes p
JOIN exercices e ON e.nom = 'Montées de genoux'
WHERE p.nom = 'Cardio intervalle débutant'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 50
FROM programmes p
JOIN exercices e ON e.nom = 'Jumping jacks'
WHERE p.nom = 'Cardio intervalle débutant'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 90
FROM programmes p
JOIN exercices e ON e.nom = 'Corde à sauter'
WHERE p.nom = 'Endurance active maison'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 45
FROM programmes p
JOIN exercices e ON e.nom = 'Planche dynamique'
WHERE p.nom = 'Endurance active maison'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 60
FROM programmes p
JOIN exercices e ON e.nom = 'Course légère sur place'
WHERE p.nom = 'Endurance active maison'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 40
FROM programmes p
JOIN exercices e ON e.nom = 'Mountain climbers'
WHERE p.nom = 'HIIT métabolique 20 min'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 12
FROM programmes p
JOIN exercices e ON e.nom = 'Thrusters haltères'
WHERE p.nom = 'HIIT métabolique 20 min'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 16
FROM programmes p
JOIN exercices e ON e.nom = 'Fentes marchées'
WHERE p.nom = 'HIIT métabolique 20 min'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 16
FROM programmes p
JOIN exercices e ON e.nom = 'Fentes marchées'
WHERE p.nom = 'Perte de poids progressive'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 120
FROM programmes p
JOIN exercices e ON e.nom = 'Corde à sauter'
WHERE p.nom = 'Perte de poids progressive'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 40
FROM programmes p
JOIN exercices e ON e.nom = 'Mountain climbers'
WHERE p.nom = 'Perte de poids progressive'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 45
FROM programmes p
JOIN exercices e ON e.nom = 'Course légère sur place'
WHERE p.nom = 'Reprise en douceur maison'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 10
FROM programmes p
JOIN exercices e ON e.nom = 'Squat au poids du corps'
WHERE p.nom = 'Reprise en douceur maison'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 30
FROM programmes p
JOIN exercices e ON e.nom = 'Jumping jacks'
WHERE p.nom = 'Reprise en douceur maison'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 1, 12
FROM programmes p
JOIN exercices e ON e.nom = 'Sprint vélo stationnaire'
WHERE p.nom = 'Perte de poids intensité progressive'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 2, 30
FROM programmes p
JOIN exercices e ON e.nom = 'Skaters latéraux'
WHERE p.nom = 'Perte de poids intensité progressive'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);

INSERT INTO programme_exercice (programme_id, exercice_id, ordre, repetitions)
SELECT p.id, e.id, 3, 20
FROM programmes p
JOIN exercices e ON e.nom = 'Kettlebell swing'
WHERE p.nom = 'Perte de poids intensité progressive'
  AND NOT EXISTS (SELECT 1 FROM programme_exercice pe WHERE pe.programme_id = p.id AND pe.exercice_id = e.id);
