import { FC } from "react";
import { ContainerPage } from "../../containers";
import { styles } from "../../styles";
import { frontend } from "../../assets";
import ContentTechno from "../ContentTechno";
import {
  language_programming,
  framework,
  stylePaging,
  sgbd,
} from "../../constants/Data";

const Tech: FC = () => {
  return (
    <ContainerPage>
      <section className="m-3" id="tech">
        <h1
          className={`${styles.titleStyle} text-lg mb-6 text-center font-semibold text-white `}
          style={{ fontSize: "2rem" }}
        >
          Mes stacks
        </h1>
        <div className="grid sm:grid-cols-1 lg:grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-3 mb-4">
          <ContentTechno
            image={frontend}
            title="Langages de programmations"
            stack={language_programming}
          />
          <ContentTechno
            image={frontend}
            title="Frameworks"
            stack={framework}
          />

          <ContentTechno
            image={frontend}
            title="Mise en page et styles"
            stack={stylePaging}
          />
          <ContentTechno
            image={frontend}
            title="Systeme de gestion de base de donnée"
            stack={sgbd}
          />
        </div>
      </section>
    </ContainerPage>
  );
};

export default Tech;
