<?php
if (!defined('_ECRIRE_INC_VERSION')) {
	return;
}

// fonction pour le pipeline, n'a rien a effectuer
function contrib_autoriser() {
}

/**
 * Autorisation minimale d'accès à toutes les pages du plugin Contrib.
 * Par défaut, seuls les administrateurs complets sont autorisés à utiliser le plugin.
 * Cette autorisation est à la base de la plupart des autres autorisations du plugin.
 *
 * @param $faire
 * @param $type
 * @param $id
 * @param $qui
 * @param $options
 *
 * @return bool
 */
function autoriser_contrib_dist($faire, $type, $id, $qui, $options) {
	return autoriser('defaut');
}

/**
 * Autorisation de modifier le champ extra catégorie d'une rubrique.
 * Il faut :
 * - être un webmestre,
 * - et que si la rubrique est un secteur :
 * -- qu'elle soit en création ou sinon que son secteur ne soit ni un secteur-carnet, ni un secteur-apropos,
 *    ni un secteur-galaxie.
 * - ou si la rubrique n'est pas un secteur :
 * -- qu'elle ait une profondeur égale 1,
 * -- que son secteur ne soit ni un secteur-carnet, ni un secteur-apropos, ni un secteur-galaxie.
 * -- et qu'il ait déjà sa catégorie remplie.
 *
 *
 * @param $faire
 * 		L'action se nomme modifierextra
 * @param $type
 * 		Le type est toujours rubrique.
 * @param $id
 * 		Id de la rubrique concernée.
 * @param $qui
 * 		L'auteur connecté
 * @param $options
 *      Contient le contexte de la saisie mais n'est pas utilisé.
 * @param mixed $opt
 *
 * @return bool
 */
function autoriser_rubrique_modifierextra_categorie($faire, $type, $id, $qui, $opt) {

	// Par défaut la modification est interdite.
	$autoriser = false;

	// Seuls les webmestres peuvent configurer la catégorie d'une rubrique.
	if (autoriser('webmestre')) {
		include_spip('inc/contrib_rubrique');
		$id_parent = isset($opt['contexte']['id_parent']) ? intval($opt['contexte']['id_parent']) : null;
		if (!is_null($id_parent)) {
			if (!$id_parent) {
				// La rubrique parent est nulle: la rubrique en cours de création ou de modification est un secteur.
				$id_rubrique = intval($id);
				if (!$id_rubrique) {
					// Création d'un secteur: on autorise car c'est plus surement un secteur plugin.
					$autoriser = true;
				} else {
					// Modification d'un secteur: on autorise si ce n'est pas apropos, carnet ou galaxie sinon
					// l'autorisation est refusée.
					if (!rubrique_dans_secteur_apropos($id_rubrique)
						and !rubrique_dans_secteur_carnet($id_rubrique)
						and !rubrique_dans_secteur_galaxie($id_rubrique)) {
						$autoriser = true;
					}
				}
			} else {
				// La rubrique parent est a minima un secteur. Il faut donc tester la profondeur de la rubrique
				// en cours de traitement qui ne doit pas dépasser 1.
				// On vérifie si la rubrique parent est dans un secteur à exclure (non plugin).
				// - le carnet wiki
				// - le secteur apropos
				// - le secteur galaxie
				if (!rubrique_dans_secteur_apropos($id_parent)
					and !rubrique_dans_secteur_carnet($id_parent)
					and !rubrique_dans_secteur_galaxie($id_parent)) {
					// On vérifie la profondeur de la rubrique parent est un secteur et qu'elle a une catégorie
					// non vide.
					$parent = rubrique_lire($id_parent);
					$profondeur = intval($parent['profondeur']);
					if (($profondeur == 0)
						and ($parent['categorie'])) {
						$autoriser = true;
					}
				}
			}
		}
	}

	return $autoriser;
}

/**
 * Autorisation de modifier le champ extra préfixe d'une rubrique.
 * Il faut :
 * - être un webmestre,
 * - que la rubrique ait une profondeur égale à 2,
 * - que la catégorie de sa rubrique parente soit non vide (rubrique-categorie) ce qui n'est possible que si
 *   la rubrique est dans un secteur-plugin.
 * Cela implique que le préfixe ne peut être positionné que si les rubriques parentes
 * ont déjà une catégorie.
 *
 * @param $faire
 * 		L'action se nomme modifierextra
 * @param $type
 * 		Le type est toujours rubrique.
 * @param $id
 * 		Id de la rubrique concernée.
 * @param $qui
 * 		L'auteur connecté
 * @param $options
 *      Contient le contexte de la saisie mais n'est pas utilisé.
 * @param mixed $opt
 *
 * @return bool
 */
function autoriser_rubrique_modifierextra_prefixe($faire, $type, $id, $qui, $opt) {

	// Par défaut la modification est interdite.
	$autoriser = false;

	// Seuls les webmestres peuvent configurer le préfixe d'une rubrique-plugin.
	if (autoriser('webmestre')) {
		$id_parent = isset($opt['contexte']['id_parent']) ? intval($opt['contexte']['id_parent']) : 0;
		if ($id_parent) {
			// On vérifie la profondeur de la rubrique parent qui ne peut-être que 2 et le remplissage de sa
			// catégorie.
			include_spip('inc/contrib_rubrique');
			$parent = rubrique_lire($id_parent);
			$profondeur = intval($parent['profondeur']);
			if (($profondeur == 1)
				and $parent['categorie']) {
				$autoriser = true;
			}
		}
	}

	return $autoriser;
}

/**
 * Autorisation d'affichage du menu d'accès à gestion des typologies de plugin (page=svptype_typologie).
 * Il faut être autorisé à utiliser le plugin.
 *
 * @param $faire
 * @param $type
 * @param $id
 * @param $qui
 * @param $options
 *
 * @return bool
 */
function autoriser_contrib_menu_dist($faire, $type, $id, $qui, $options) {

	// Initialisation de l'autorisation
	$autoriser = autoriser('contrib');

	return $autoriser;
}
