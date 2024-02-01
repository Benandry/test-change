import { FC } from "react";
import { ContainerPage } from "../containers";
import { styles } from "../styles";

const Who: FC = () => {
  return (
    <ContainerPage>
      <section className="pb-4" id="me">
        <h1
          className={`${styles.titleStyle} mb-6 text-center font-semibold text-white `}
        >
          Qui suis-je ?
        </h1>
        <div className="info text-center text-white">
          Mon objectif est de créer des applications web performantes et
          intuitives en utilisant les dernières technologies et les meilleures
          pratiques de développement. Je suis passionné par la création de
          solutions logicielles qui répondent aux besoins de mes clients.
        </div>
      </section>
    </ContainerPage>
  );
};

export default Who;
