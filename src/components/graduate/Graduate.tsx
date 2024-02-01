import { FC } from "react";
import { ContainerPage } from "../../containers";
import { styles } from "../../styles";

const Graduate: FC = () => {
  return (
    <ContainerPage>
      <section className="mb-3" id="graduates">
        <h1
          className={`${styles.titleStyle} mb-6 text-center font-semibold text-white `}
        >
          Formation professionnelles
        </h1>
      </section>
    </ContainerPage>
  );
};

export default Graduate;
