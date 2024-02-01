import { FC } from "react";
import { ContainerPage } from "../../containers";
import { skills } from "../../constants/Data";
import Card from "../../UI/Card";
import { styles } from "../../styles";

const Skills: FC = () => {
  return (
    <ContainerPage>
      <section className="mb-3" id="skills">
        <h1
          className={`${styles.titleStyle} mb-6 text-center font-semibold text-white `}
        >
          Compétences Professionnelles
        </h1>
        <div
          className={` grid sm:grid-cols-1 lg:grid-cols-2 md:grid-cols-2 xl:grid-cols-3 gap-4 justify-center  items-center sm:mb-20 mb-6`}
        >
          {skills.map(({ id, title, text, logo }) => (
            <Card key={id} logo={logo} title={title} paragraph={text} />
          ))}
        </div>
      </section>
    </ContainerPage>
  );
};

export default Skills;
