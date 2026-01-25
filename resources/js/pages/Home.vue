<script setup lang="ts">
</script>

<template>
    <main>
        <img class="head" src="/storage/uploads/Banniere_manaclysme.png" alt="Banière manaclysme">
        <div class="home_header">
            <img src="/storage/uploads/back_header.png" alt="">
            <div class="home_title">
                <h1>Vivez l’expérience d’un Grandeur Nature</h1>
                <div>
                    MANACLYSME est un grandeur nature à but non lucratif qui propose une expérience
                    immersive de jeu de rôle dans un monde fantastique médiéval conçus pour les plus jeunes
                    qui ne serait pas encore prêts aux grands GN. Les joueurs participent à des activités
                    coopératives, théâtrales et de combat en plein air, sur un vaste terrain boisé. L'objectif
                    est de partager des moments de plaisir, de réflexion éthique et de dépassement de soi.
                </div>
            </div>
        </div>
        <div class="separator"></div>
        <p>
            Pour plus d'information aller voir sur
            <a target="_blank" href="https://www.facebook.com/groups/1121679705363439/" rel="noopener noreferrer">
                notre Facebook</a>

        </p>
        </main>
</template>

<style scoped>
a {
    color: lightblue;
    text-decoration: underline;
    text-decoration-color: skyblue;
    z-index: 1;
}
.head {
    width: 100%;
    mask-image: linear-gradient(to top, black 80%, transparent);
}
.home_header {
    margin-top: -10%;
    z-index: -2;
    width: 100%;
    position: relative;
}
.home_header img{
    width: 100%;
    height: auto;
}
.home_title {
    color: white;
    position: absolute;
    top: 50%;
    translate: 0 -50%;
}
.home_title h1 {
    font-weight: bold;
    font-size: medium;
    text-align: center;
}
.home_title div {
    margin-top: 0.5rem;
    text-align: center;
    font-size: x-small;
}
.separator {
    height: 1rem;
    width: 100%;
    background-color: lightgray;
}
@media (min-width: 500px) {
    .home_title h1 {
        font-weight: bold;
    }
    .home_title div {
        padding: 1rem 3rem;
        font-size: small;
    }
}

@media (min-width: 750px) {
    .home_title h1 {
        font-weight: bold;
        font-size: xx-large;
        text-align: left;
    }
    .home_title div {
        text-align: left;
        font-size: medium;
        width: 70%;
    }
    .home_title {
        left: 10%;
    }
}
</style>
