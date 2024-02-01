import { FC } from "react";
import { styles } from "../styles";
import { IContainer } from "../interface/IContainer";

const ContainerPage: FC<IContainer> = ({ children }) => {
  return (
    <div className={`bg-primary ${styles.paddingX} ${styles.flexCenter}`}>
      <div className={`${styles.boxWidth}`}>{children}</div>
    </div>
  );
};

export default ContainerPage;
