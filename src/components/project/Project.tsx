import { FC } from "react";
import { ContainerPage } from "../../containers";
import { styles } from "../../styles";
import { bill } from "../../assets";
import ContentProject from "../ContentProject";

const Project: FC = () => {
  return (
    <ContainerPage>
      <section
        id="projects"
        className={`flex md:flex-row flex-col ${styles.paddingY} flex-wrap`}
      >
        <ContentProject
          image={bill}
          company="Dev web service "
          title="Developpeur web"
          text="Developpeur"
        />
        <ContentProject
          image={bill}
          company="Dev web service "
          title="Developpeur web"
          text="Developpeur"
        />
        <ContentProject
          image={bill}
          company="Fidev "
          title="Developpeur web"
          text="Developpeur"
        />
        <ContentProject
          image={bill}
          company="Paositra Malagasy "
          title="Developpeur web"
          text="Developpeur Application back office"
        />
      </section>
    </ContainerPage>
  );
};

export default Project;
