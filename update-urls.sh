#!/bin/bash

# Script pour remplacer les URLs localhost par la configuration dynamique
echo "🔄 Mise à jour des URLs API dans les fichiers JavaScript..."

# Fonction pour remplacer les URLs dans un fichier
replace_urls() {
    local file="$1"
    echo "  📝 Traitement de $file"
    
    # Remplacer les URLs localhost par getApiUrl()
    sed -i '' 's|http://localhost:8000/api/car/list|getApiUrl(API_CONFIG.ENDPOINTS.CAR.LIST)|g' "$file"
    sed -i '' 's|http://localhost:8000/api/car/add|getApiUrl(API_CONFIG.ENDPOINTS.CAR.ADD)|g' "$file"
    sed -i '' 's|http://localhost:8000/api/car/delete/|getApiUrl(API_CONFIG.ENDPOINTS.CAR.DELETE + "/")|g' "$file"
    
    sed -i '' 's|http://localhost:8000/api/trip/all|getApiUrl(API_CONFIG.ENDPOINTS.TRIP.ALL)|g' "$file"
    sed -i '' 's|http://localhost:8000/api/trip/add|getApiUrl(API_CONFIG.ENDPOINTS.TRIP.ADD)|g' "$file"
    sed -i '' 's|http://localhost:8000/api/trip/search|getApiUrl(API_CONFIG.ENDPOINTS.TRIP.SEARCH)|g' "$file"
    sed -i '' 's|http://localhost:8000/api/trip/delete/|getApiUrl(API_CONFIG.ENDPOINTS.TRIP.DELETE + "/")|g' "$file"
    
    sed -i '' 's|http://localhost:8000/api/user|getApiUrl(API_CONFIG.ENDPOINTS.AUTH.REGISTER)|g' "$file"
    
    # Remplacer les URLs d'images
    sed -i '' 's|http://localhost:8000\${photoUrl}|API_CONFIG.BASE_URL + photoUrl|g' "$file"
}

# Traiter tous les fichiers JavaScript
for file in js/*.js; do
    if [ -f "$file" ]; then
        replace_urls "$file"
    fi
done

echo "✅ Mise à jour terminée !"
