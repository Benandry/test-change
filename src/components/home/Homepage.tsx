import { FC } from "react";
import { ContainerHome } from "../../containers";
import { styles } from "../../styles";

import { discount, my_image } from "../../assets";

const Homepage: FC = () => {
  return (
    <ContainerHome>
      <section className={`${styles.paddingY}  `} id="home">
        <div id="home" className={`flex md:flex-row  flex-col-reverse   `}>
          <div
            className={`flex-1 ${styles.flexStart} flex-col xl:px-0 sm:px-16 px-6`}
          >
            <div className="flex flex-row items-center py-[6px] px-4 bg-discount-gradient rounded-[10px] mb-2">
              <img
                src={discount}
                alt="discount"
                className="w-[32px] h-[32px]"
              />
              <p className={`${styles.paragraph} ml-1`}>
                <span className="text-white">Hello </span> Bienvenu sur mon site
                de
                <span className="text-white"> portfolio </span>
              </p>
            </div>

            <div className="flex flex-row justify-between items-center w-full">
              <h1
                className={`text-white flex-1 text-2xl font-poppins font-semibold title__home xs:mt-4 `}
              >
                Je suis <br />
                <span className="">développeur FullStack JavaScript. </span>
                <br className="sm:block hidden" />
              </h1>
            </div>
            <div className="flex flex-row justify-start gap-8 items-center w-full">
              <div className="btn_cta">
                <a
                  href="/#contact"
                  className="bg-transparent text-white py-2 px-4 border rounded"
                >
                  MOn contact
                </a>
              </div>
              <div className="btn_cta">
                <a
                  href="/#contact"
                  className="bg-transparent text-white py-2 px-4 border  rounded"
                >
                  Mon CV
                </a>
              </div>
            </div>
          </div>
          <div
            className={`flex-1 flex ${styles.flexCenter} md:my-0 my-10 relative`}
          >
            <img
              src={my_image}
              alt="billing"
              className=" rounded-full relative my_image"
            />

            {/* gradient start */}
            <div className="absolute z-[0] w-[40%] h-[35%] top-0 pink__gradient" />
            <div className="absolute z-[1] w-[80%] h-[80%] rounded-full white__gradient bottom-40" />
            <div className="absolute z-[0] w-[50%] h-[50%] right-20 bottom-20 blue__gradient" />
            {/* gradient end */}
          </div>
        </div>
      </section>
    </ContainerHome>
  );
};

export default Homepage;
