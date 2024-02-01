import { FC } from "react";
import { layout, styles } from "../styles";
import { Content } from "../interface/IProject";

const ContentProject: FC<Content> = ({ image, company, title, text }) => {
  return (
    <div className={layout.sectionImgReverse}>
      <div className="bg-primary border border-gray-200 rounded-lg shadow hover:bg-blue-900 dark:bg-blue-800 dark:border-blue-700 dark:hover:bg-blue-70">
        <h1
          className={`${styles.titleStyle} text-lg mb-6 text-center font-semibold text-white `}
          style={{ fontSize: "1rem" }}
        >
          {company}
        </h1>

        <h2
          className={`${styles.titleStyle} text-lg mb-6 text-center font-semibold text-white `}
          style={{ fontSize: "1rem" }}
        >
          {title}
        </h2>

        <img
          src={image}
          alt="billing"
          className="w-[100%] h-[100%] relative z-[5]"
        />
        <p>{text}</p>
      </div>

      {/* gradient start */}
      <div className="absolute z-[3] -left-1/2 top-0 w-[50%] h-[50%] rounded-full white__gradient" />
      <div className="absolute z-[0] w-[50%] h-[50%] -left-1/2 bottom-0 rounded-full pink__gradient" />
      {/* gradient end */}
    </div>
  );
};

export default ContentProject;
