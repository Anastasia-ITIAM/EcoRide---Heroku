#!/bin/bash

echo "🔍 Test de l'API des avis MongoDB..."

# Test de l'API des avis sans authentification (devrait retourner une erreur 401)
echo "📝 Test 1: API des avis sans token JWT"
response=$(curl -s -w "%{http_code}" "https://ecoecoride-bfc4b6ed3554.herokuapp.com/api/trip/4/reviews")
http_code="${response: -3}"
body="${response%???}"

echo "Code HTTP: $http_code"
echo "Réponse: $body"
echo ""

# Test de l'API de test MongoDB
echo "📝 Test 2: API de test MongoDB"
response2=$(curl -s -w "%{http_code}" "https://ecoecoride-bfc4b6ed3554.herokuapp.com/api/test")
http_code2="${response2: -3}"
body2="${response2%???}"

echo "Code HTTP: $http_code2"
echo "Réponse: $body2"
echo ""

echo "✅ Tests terminés"
